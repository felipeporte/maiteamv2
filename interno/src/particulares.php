<?php
declare(strict_types=1);

function particulares_config(): array { return db()->query('SELECT * FROM particular_configuracion WHERE id=1')->fetch() ?: []; }

function particulares_cotizar(int $base, int $medioId): array {
    $stmt=db()->prepare('SELECT porcentaje, aplica_iva FROM particular_medios_pago WHERE id=:id AND activo=1'); $stmt->execute(['id'=>$medioId]); $m=$stmt->fetch();
    if (!$m) throw new RuntimeException('Medio de pago no disponible');
    $rate=(float)$m['porcentaje'] * ((int)$m['aplica_iva'] ? 1+(float)particulares_config()['iva_comision'] : 1);
    $charged=(int)ceil($base/(1-$rate)); return ['base_amount'=>$base,'payment_fee'=>$charged-$base,'charged_amount'=>$charged,'effective_fee_rate'=>$rate];
}

function particulares_crear_pago(int $reservaId,int $medioId,array $cotizacion): int { $s=db()->prepare("INSERT INTO particular_pagos(reserva_id,medio_pago_id,base_amount,payment_fee,charged_amount,status) VALUES(:r,:m,:b,:f,:c,'pending')"); $s->execute(['r'=>$reservaId,'m'=>$medioId,'b'=>$cotizacion['base_amount'],'f'=>$cotizacion['payment_fee'],'c'=>$cotizacion['charged_amount']]); return (int)db()->lastInsertId(); }

function mercadopago_crear_preferencia(int $pagoId,string $descripcion,int $monto,string $externalReference): string { $token=env('MERCADOPAGO_ACCESS_TOKEN'); if(!$token) throw new RuntimeException('Falta MERCADOPAGO_ACCESS_TOKEN'); $return=rtrim((string)env('PARTICULARES_PUBLIC_URL','http://localhost:8080/particulares/'),'/').'/'; $payload=json_encode(['items'=>[['title'=>$descripcion,'quantity'=>1,'currency_id'=>'CLP','unit_price'=>(float)$monto]],'external_reference'=>$externalReference,'back_urls'=>['success'=>$return,'failure'=>$return,'pending'=>$return],'auto_return'=>'approved']); $ch=curl_init('https://api.mercadopago.com/checkout/preferences'); curl_setopt_array($ch,[CURLOPT_POST=>true,CURLOPT_HTTPHEADER=>['Authorization: Bearer '.$token,'Content-Type: application/json'],CURLOPT_POSTFIELDS=>$payload,CURLOPT_RETURNTRANSFER=>true,CURLOPT_TIMEOUT=>20]); $raw=curl_exec($ch); $curlError=curl_error($ch); $code=(int)curl_getinfo($ch,CURLINFO_HTTP_CODE); curl_close($ch); $data=json_decode((string)$raw,true); if($code<200||$code>=300||empty($data['init_point'])) { error_log('Mercado Pago preference error: http='.$code.' curl='.($curlError?:'none').' response='.substr((string)$raw,0,1000)); throw new RuntimeException('Mercado Pago no pudo crear la preferencia'); } db()->prepare('UPDATE particular_pagos SET provider_payment_id=:p WHERE id=:id')->execute(['p'=>$data['id']??null,'id'=>$pagoId]); return (string)$data['init_point']; }

function mercadopago_crear_preferencia_v2(int $pagoId,string $descripcion,int $monto,string $externalReference): string { $token=env('MERCADOPAGO_ACCESS_TOKEN'); if(!$token) throw new RuntimeException('Falta MERCADOPAGO_ACCESS_TOKEN'); $return=rtrim((string)env('PARTICULARES_PUBLIC_URL','http://localhost:8080/particulares/'),'/').'/'; $d=['items'=>[['title'=>$descripcion,'quantity'=>1,'currency_id'=>'CLP','unit_price'=>(float)$monto]],'external_reference'=>$externalReference,'back_urls'=>['success'=>$return,'failure'=>$return,'pending'=>$return]]; if(!str_contains($return,'localhost')&&!str_contains($return,'127.0.0.1')) $d['auto_return']='approved'; $ch=curl_init('https://api.mercadopago.com/checkout/preferences'); curl_setopt_array($ch,[CURLOPT_POST=>true,CURLOPT_HTTPHEADER=>['Authorization: Bearer '.$token,'Content-Type: application/json'],CURLOPT_POSTFIELDS=>json_encode($d),CURLOPT_RETURNTRANSFER=>true,CURLOPT_TIMEOUT=>20]); $raw=curl_exec($ch); $code=(int)curl_getinfo($ch,CURLINFO_HTTP_CODE); curl_close($ch); $data=json_decode((string)$raw,true); if($code<200||$code>=300||empty($data['init_point'])) throw new RuntimeException('Mercado Pago no pudo crear la preferencia'); db()->prepare('UPDATE particular_pagos SET provider_payment_id=:p WHERE id=:id')->execute(['p'=>$data['id']??null,'id'=>$pagoId]); return (string)$data['init_point']; }

function particulares_reservar(int $bloqueId,int $clienteId,int $deportistaId): int {
    $pdo=db(); $pdo->beginTransaction();
    try { $s=$pdo->prepare('SELECT id,valor_base,activo FROM particular_bloques WHERE id=:id FOR UPDATE'); $s->execute(['id'=>$bloqueId]); $b=$s->fetch(); if(!$b||!(int)$b['activo']) throw new RuntimeException('Bloque no disponible');
        $s=$pdo->prepare('SELECT o.bloque_id,o.reserva_id,r.estado,r.expira_at FROM particular_ocupaciones o JOIN particular_reservas r ON r.id=o.reserva_id WHERE o.bloque_id=:id FOR UPDATE'); $s->execute(['id'=>$bloqueId]); $o=$s->fetch();
        if($o && !($o['estado']==='pending' && $o['expira_at'] && strtotime($o['expira_at'])<time())) throw new RuntimeException('Bloque ya reservado');
        if($o) { $pdo->prepare("UPDATE particular_reservas SET estado='expired' WHERE id=:rid")->execute(['rid'=>$o['reserva_id']]); $pdo->prepare('DELETE FROM particular_ocupaciones WHERE bloque_id=:id')->execute(['id'=>$bloqueId]); }
        $exp=date('Y-m-d H:i:s',time()+((int)particulares_config()['retencion_min']*60)); $s=$pdo->prepare("INSERT INTO particular_reservas (bloque_id,cliente_id,deportista_id,expira_at,base_amount) VALUES (:b,:c,:d,:e,:a)"); $s->execute(['b'=>$bloqueId,'c'=>$clienteId,'d'=>$deportistaId,'e'=>$exp,'a'=>$b['valor_base']]); $id=(int)$pdo->lastInsertId(); $pdo->prepare('INSERT INTO particular_ocupaciones (bloque_id,reserva_id) VALUES (:b,:r)')->execute(['b'=>$bloqueId,'r'=>$id]); $pdo->commit(); return $id;
    } catch(Throwable $e){$pdo->rollBack(); throw $e;}
}

function particulares_reservar_publico(int $bloqueId,string $clienteNombre,string $email,string $telefono,string $deportistaNombre): int { $pdo=db(); $s=$pdo->prepare('INSERT INTO particular_clientes(nombre,email,telefono) VALUES(:n,:e,:t)'); $s->execute(['n'=>$clienteNombre,'e'=>$email,'t'=>$telefono?:null]); $c=(int)$pdo->lastInsertId(); $s=$pdo->prepare('INSERT INTO particular_deportistas(cliente_id,nombre) VALUES(:c,:n)'); $s->execute(['c'=>$c,'n'=>$deportistaNombre]); $d=(int)$pdo->lastInsertId(); return particulares_reservar($bloqueId,$c,$d); }

function particulares_agenda(string $fecha): array {
    $s=db()->prepare("SELECT b.id,b.fecha,b.inicio,b.fin,b.valor_base,m.nombre monitor, r.id reserva_id,r.estado FROM particular_bloques b JOIN particular_monitores m ON m.id=b.monitor_id LEFT JOIN particular_ocupaciones o ON o.bloque_id=b.id LEFT JOIN particular_reservas r ON r.id=o.reserva_id AND NOT (r.estado='pending' AND r.expira_at<NOW()) WHERE b.fecha=:f AND b.activo=1 ORDER BY b.inicio,m.nombre"); $s->execute(['f'=>$fecha]); return $s->fetchAll();
}

function particulares_generar_bloques(string $desde, string $hasta): int {
    $cfg=particulares_config(); $dur=(int)($cfg['duracion_min']??60); $pdo=db(); $n=0;
    $mon=$pdo->query('SELECT id FROM particular_monitores WHERE activo=1')->fetchAll(PDO::FETCH_COLUMN);
    $rules=$pdo->query('SELECT monitor_id,dia_semana,hora_inicio,hora_fin FROM particular_disponibilidad WHERE activo=1')->fetchAll();
    for($d=new DateTime($desde);$d->format('Y-m-d')<=$hasta;$d->modify('+1 day')) { $dow=(int)$d->format('w'); foreach($rules as $r) if((int)$r['dia_semana']===$dow) foreach($mon as $mid) if($r['monitor_id']===null||(int)$r['monitor_id']===(int)$mid) { $start=new DateTime($d->format('Y-m-d').' '.$r['hora_inicio']); $end=new DateTime($d->format('Y-m-d').' '.$r['hora_fin']); while($start<$end){$finish=(clone $start)->modify("+$dur minutes"); if($finish>$end) break; $s=$pdo->prepare('INSERT IGNORE INTO particular_bloques (monitor_id,fecha,inicio,fin,valor_base) VALUES (:m,:f,:i,:e,:v)');$s->execute(['m'=>$mid,'f'=>$d->format('Y-m-d'),'i'=>$start->format('Y-m-d H:i:s'),'e'=>$finish->format('Y-m-d H:i:s'),'v'=>$cfg['valor_base']]);$n+=(int)$s->rowCount();$start=$finish;}}
    } return $n;
}
