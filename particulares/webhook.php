<?php
declare(strict_types=1);
require __DIR__ . '/../interno/src/bootstrap.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); exit; }
$body=json_decode((string)file_get_contents('php://input'),true) ?: [];
$paymentId=(string)($body['data']['id']??$_GET['data.id']??'');
if($paymentId===''){ http_response_code(200); exit; }
$token=env('MERCADOPAGO_ACCESS_TOKEN'); if(!$token){http_response_code(500);exit;}
$ch=curl_init('https://api.mercadopago.com/v1/payments/'.rawurlencode($paymentId)); curl_setopt_array($ch,[CURLOPT_HTTPHEADER=>['Authorization: Bearer '.$token],CURLOPT_RETURNTRANSFER=>true,CURLOPT_TIMEOUT=>20]); $raw=curl_exec($ch); $code=(int)curl_getinfo($ch,CURLINFO_HTTP_CODE); curl_close($ch); $payment=json_decode((string)$raw,true);
if($code<200||$code>=300||!is_array($payment)){http_response_code(202);exit;}
$ref=(string)($payment['external_reference']??''); if(!preg_match('/^particular_reserva_(\d+)$/',$ref,$m)){http_response_code(200);exit;} $status=(string)($payment['status']??'');
$pdo=db(); $pdo->beginTransaction(); try { $s=$pdo->prepare('SELECT id,reserva_id,charged_amount FROM particular_pagos WHERE reserva_id=:r ORDER BY id DESC LIMIT 1 FOR UPDATE');$s->execute(['r'=>(int)$m[1]]);$p=$s->fetch(); if(!$p){$pdo->rollBack();http_response_code(200);exit;} if($status==='approved'){$pdo->prepare("UPDATE particular_pagos SET status='paid',provider_payment_id=:pid,paid_at=NOW() WHERE id=:id AND status<>'paid'")->execute(['pid'=>$paymentId,'id'=>$p['id']]);$pdo->prepare("UPDATE particular_reservas SET estado='confirmed' WHERE id=:id AND estado IN ('pending','awaiting_payment')")->execute(['id'=>$p['reserva_id']]);}else{$pdo->prepare('UPDATE particular_pagos SET status=:s,provider_payment_id=:pid WHERE id=:id')->execute(['s'=>$status,'pid'=>$paymentId,'id'=>$p['id']]);} $pdo->commit(); http_response_code(200);}catch(Throwable $e){$pdo->rollBack();http_response_code(500);}
