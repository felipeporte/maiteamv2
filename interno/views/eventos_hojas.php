<?php
/** @var array|null $evento */
/** @var array $inscripciones */
/** @var array $hojas_elementos */
/** @var bool $hojas_schema_ready */
/** @var array|null $hoja_context */
/** @var array $hoja_form */
/** @var int $hoja_selected_inscripcion_id */

$hojaTemplates = evento_federado_hoja_template_catalog();
$selectedTemplateCode = (string) ($hoja_form['plantilla_codigo'] ?? '');
if (!array_key_exists($selectedTemplateCode, $hojaTemplates)) {
    $selectedTemplateCode = array_key_first($hojaTemplates) ?: 'freeskating_short';
}

$selectedTemplate = $hojaTemplates[$selectedTemplateCode] ?? reset($hojaTemplates);
$rowCount = max(1, evento_federado_hoja_template_rows($selectedTemplateCode));
$sheetYear = date('Y');

$sheetTitles = [
    'freeskating_short' => 'SHORT PROGRAM CONTENT SHEET ' . $sheetYear,
    'freeskating_free' => 'FREE PROGRAM CONTENT SHEET ' . $sheetYear,
    'solo_dance_style' => 'STYLE DANCE CONTENT SHEET ' . $sheetYear,
    'solo_dance_free' => 'FREE DANCE CONTENT SHEET ' . $sheetYear,
    'nacional_formativo_escuela_d' => 'HOJA DE CONTENIDO TECNICO ' . $sheetYear,
];
$sheetSubtitles = [
    'freeskating_short' => 'FREESKATE',
    'freeskating_free' => 'FREESKATE',
    'solo_dance_style' => 'SOLO DANCE',
    'solo_dance_free' => 'SOLO DANCE',
    'nacional_formativo_escuela_d' => 'NIVELES FORMATIVOS Y ESCUELAS',
];
$sheetSectionTitles = [
    'freeskating_short' => 'ELEMENTS OF THE PROGRAM',
    'freeskating_free' => 'ELEMENTS OF THE PROGRAM',
    'solo_dance_style' => 'ELEMENTS OF THE PROGRAM',
    'solo_dance_free' => 'ELEMENTS OF THE PROGRAM',
    'nacional_formativo_escuela_d' => 'ELEMENTOS DEL PROGRAMA',
];
$sheetFileSuffixes = [
    'freeskating_short' => 'ShortProgram',
    'freeskating_free' => 'FreeProgram',
    'solo_dance_style' => 'StyleDance',
    'solo_dance_free' => 'FreeDance',
    'nacional_formativo_escuela_d' => 'ContenidoTecnico',
];

$sheetTitle = $sheetTitles[$selectedTemplateCode] ?? ((string) ($selectedTemplate['label'] ?? 'CONTENT SHEET') . ' ' . $sheetYear);
$sheetSubtitle = $sheetSubtitles[$selectedTemplateCode] ?? (string) ($selectedTemplate['short_label'] ?? 'CONTENT SHEET');
$sheetSectionTitle = $sheetSectionTitles[$selectedTemplateCode] ?? 'ELEMENTS OF THE PROGRAM';
$sheetFileSuffix = $sheetFileSuffixes[$selectedTemplateCode] ?? 'ContentSheet';

$selectedInscripcion = null;
foreach ($inscripciones as $inscripcionRow) {
    if ((int) ($inscripcionRow['id'] ?? 0) === (int) $hoja_selected_inscripcion_id) {
        $selectedInscripcion = $inscripcionRow;
        break;
    }
}
if ($selectedInscripcion === null && !empty($inscripciones)) {
    $selectedInscripcion = $inscripciones[0];
}

$selectedInscripcionLabel = $selectedInscripcion !== null
    ? trim((string) ($selectedInscripcion['deportista_nombre'] ?? '') . ' · ' . (string) ($selectedInscripcion['modalidad_nombre'] ?? ''))
    : '';

$initialRows = array_values(array_filter(
    array_slice((array) ($hoja_form['rows'] ?? []), 0, $rowCount),
    static fn ($row): bool => is_array($row)
));

while (count($initialRows) < $rowCount) {
    $initialRows[] = [
        'time' => '',
        'code' => '',
        'notes' => '',
    ];
}

$initialValues = [
    'competitor' => (string) ($hoja_form['competidor_nombre'] ?? ''),
    'category' => (string) ($hoja_form['categoria_label'] ?? ''),
    'fedtype' => 'FEDERATION',
    'federation' => (string) ($hoja_form['club'] ?? ''),
    'music' => (string) ($hoja_form['choreography'] ?? ''),
    'representing' => (string) ($hoja_form['representing'] ?? ($hoja_form['club'] ?? '')),
    'observations' => (string) ($hoja_form['observaciones'] ?? ''),
];

$sheetConfig = [
    'title' => $sheetTitle,
    'subtitle' => $sheetSubtitle,
    'sectionTitle' => $sheetSectionTitle,
    'rowCount' => $rowCount,
    'fileSuffix' => $sheetFileSuffix,
    'logoUrl' => base_url('/assets/img/Logo principal.png'),
    'templateCode' => $selectedTemplateCode,
    'codes' => $selectedTemplate['codes'] ?? [],
];
?>
<section class="page ficha-page eventos-page hoja-sk8info-page">
    <div class="sheet-page">
        <div id="main-hdr" class="sheet-banner">
            <div class="sheet-banner-copy">
                <p class="kicker">Content sheets</p>
                <h1><?= e($sheetTitle) ?></h1>
                <p><?= e($sheetSubtitle) ?></p>
            </div>
            <div class="sheet-banner-actions">
                <a class="button ghost" href="#eventos-detalle" data-ficha-tab data-ficha-target="eventos-detalle">Volver al detalle</a>
                <button type="button" class="button ghost" id="restore-button">Import TXT</button>
                <button type="button" class="button" id="download-button">Download PDF</button>
            </div>
        </div>

        <div id="content-hdr" class="sheet-subbanner">
            <div class="sheet-subbanner-copy">
                <strong><?= e((string) ($selectedTemplate['label'] ?? '')) ?></strong>
                <span><?= e($selectedInscripcionLabel !== '' ? $selectedInscripcionLabel : 'No inscripcion selected') ?></span>
            </div>
            <div class="sheet-subbanner-meta">
                <span><?= e((string) ($evento['nombre'] ?? '')) ?></span>
                <span><?= e($sheetSectionTitle) ?></span>
            </div>
        </div>

        <?php if ($evento === null): ?>
            <div class="alert">Selecciona un evento para administrar sus hojas de elementos.</div>
        <?php elseif (!$hojas_schema_ready): ?>
            <div class="alert danger">Falta aplicar la migracion de hojas de elementos en esta base de datos.</div>
        <?php else: ?>
            <?php if (!empty($errors)): ?>
                <div class="alert danger">
                    <?php foreach ($errors as $error): ?>
                        <p><?= e($error) ?></p>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <form id="sheet-form" class="sheet-form" method="post" action="<?= e(base_url('/?page=eventos&action=hoja&id=' . (int) $evento['id'])) ?>">
                <input type="hidden" name="action" value="hoja-save">
                <input type="hidden" name="evento_id" value="<?= e((string) $evento['id']) ?>">
                <input type="hidden" name="inscripcion_id" value="<?= e((string) $hoja_selected_inscripcion_id) ?>">
                <input type="hidden" name="plantilla_codigo" value="<?= e($selectedTemplateCode) ?>">
                <input type="hidden" name="representing" id="representing-hidden" value="<?= e($initialValues['representing']) ?>">

                <div class="sheet-form-card">
                    <div class="sheet-grid">
                        <label class="sheet-field">
                            Competitor's Name
                            <input id="sp_competitor" type="text" name="competidor_nombre" value="<?= e($initialValues['competitor']) ?>" placeholder="Name">
                        </label>

                        <label class="sheet-field">
                            Category
                            <input id="sp_category" type="text" name="categoria_label" value="<?= e($initialValues['category']) ?>" placeholder="Category">
                        </label>

                        <label class="sheet-field">
                            Fed type
                            <input id="sp_fedtype" type="text" name="fedtype" value="<?= e($initialValues['fedtype']) ?>" placeholder="FEDERATION">
                        </label>

                        <label class="sheet-field">
                            Federation
                            <input id="sp_federation" type="text" name="club" value="<?= e($initialValues['federation']) ?>" placeholder="Club MaiTeam">
                        </label>

                        <label class="sheet-field">
                            Music
                            <input id="sp_music" type="text" name="choreography" value="<?= e($initialValues['music']) ?>" placeholder="Music title">
                        </label>
                    </div>
                </div>

                <div class="sheet-form-card">
                    <div class="sheet-card-head">
                        <div>
                            <p class="form-label"><?= e($sheetSectionTitle) ?></p>
                            <p class="hint">Time is in m:ss. Select a code and the element name will auto-fill if blank.</p>
                        </div>
                        <?php if ($selectedInscripcionLabel !== ''): ?>
                            <span class="chip"><?= e($selectedInscripcionLabel) ?></span>
                        <?php endif; ?>
                    </div>

                    <div id="sp-rows" class="sheet-rows" aria-label="Element rows"></div>
                </div>

                <label class="sheet-observations">
                    Observaciones
                    <textarea id="sp_observations" name="observaciones_hoja" rows="3" placeholder="Notas opcionales para la comision tecnica"><?= e($initialValues['observations']) ?></textarea>
                </label>

                <div class="sheet-bottom-actions">
                    <button type="submit" class="button" name="hoja_submit" value="save">Guardar borrador</button>
                    <button type="button" class="button ghost" id="save-button">Export TXT</button>
                </div>
            </form>
        <?php endif; ?>

        <template id="options-template">
            <option value="">--</option>
            <?php foreach (($selectedTemplate['codes'] ?? []) as $code => $label): ?>
                <option value="<?= e((string) $code) ?>"><?= e((string) $code . ' · ' . (string) $label) ?></option>
            <?php endforeach; ?>
        </template>

        <input type="file" id="restoreFile" accept=".txt" hidden>

        <div hidden aria-hidden="true">
            <span id="sc_competitor"></span>
            <span id="sc_category"></span>
            <span id="sc_fedtype"></span>
            <span id="sc_federation"></span>
            <span id="sc_music"></span>
            <span id="sc_representing"></span>
            <?php for ($i = 1; $i <= $rowCount; $i++): ?>
                <span id="sc_time<?= e((string) $i) ?>"></span>
                <span id="sc_code<?= e((string) $i) ?>"></span>
                <span id="sc_notes<?= e((string) $i) ?>"></span>
            <?php endfor; ?>
        </div>
    </div>
</section>

<script src="https://cdn.jsdelivr.net/npm/jspdf@2.5.1/dist/jspdf.umd.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/jspdf-autotable@3.5.28/dist/jspdf.plugin.autotable.min.js"></script>
<script>
(function () {
    const BLUE = {
        hdr: ['#1565c0', '#1976d2', '#42a5f5'],
        col: ['#1565c0', '#1976d2'],
        pdf: { hdr: [21, 101, 192], col: [25, 118, 210] }
    };
    window._theme = BLUE;

    const config = <?= json_encode($sheetConfig, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?: '{}' ?>;
    const initialRows = <?= json_encode($initialRows, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?: '[]' ?>;

    const form = document.getElementById('sheet-form');
    const rowsContainer = document.getElementById('sp-rows');
    const optionsTemplate = document.getElementById('options-template');
    const downloadButton = document.getElementById('download-button');
    const saveButton = document.getElementById('save-button');
    const restoreButton = document.getElementById('restore-button');
    const restoreFile = document.getElementById('restoreFile');
    const representingHidden = document.getElementById('representing-hidden');
    const competitorField = document.getElementById('sp_competitor');
    const categoryField = document.getElementById('sp_category');
    const fedtypeField = document.getElementById('sp_fedtype');
    const federationField = document.getElementById('sp_federation');
    const musicField = document.getElementById('sp_music');

    if (!form || !rowsContainer || !optionsTemplate || !downloadButton || !saveButton || !restoreButton || !restoreFile) {
        return;
    }

    let logoData = null;
    let logoType = 'PNG';
    let logoAspect = 1;

    (function () {
        const img = new Image();
        img.onload = function () {
            logoAspect = img.naturalWidth / img.naturalHeight;
            const c = document.createElement('canvas');
            c.width = img.naturalWidth;
            c.height = img.naturalHeight;
            c.getContext('2d').drawImage(img, 0, 0);
            logoData = c.toDataURL('image/png');

            ['main-hdr', 'content-hdr'].forEach(function (id) {
                const h = document.getElementById(id);
                if (!h) {
                    return;
                }
                const i = document.createElement('img');
                i.src = config.logoUrl;
                i.alt = '';
                i.style.cssText = 'height:36px;width:auto;flex-shrink:0';
                h.prepend(i);
            });
        };
        img.src = config.logoUrl;
    })();

    function makeSelect(id) {
        const select = document.createElement('select');
        select.className = 'select';
        select.id = id;
        select.appendChild(document.importNode(optionsTemplate.content, true));
        return select;
    }

    function applyMask(field) {
        const mask = field.dataset.mask.split('');
        const strip = function (value) {
            return value.split('').filter(function (char) {
                return /\d/.test(char);
            });
        };
        const apply = function (data) {
            return mask.map(function (char) {
                return char !== '_' ? char : data.length ? data.shift() : char;
            }).join('');
        };
        const changed = function () {
            const s = field.selectionStart;
            const e = field.selectionEnd;
            field.value = apply(strip(field.value));
            field.selectionStart = s;
            field.selectionEnd = e;
        };
        ['click', 'keyup', 'input'].forEach(function (eventName) {
            field.addEventListener(eventName, changed);
        });
    }

    function buildRows(container, prefix, count, rows) {
        for (let i = 1; i <= count; i++) {
            const data = rows[i - 1] || {};
            const row = document.createElement('div');
            row.className = 'el-row' + (i % 2 === 0 ? ' even' : '');

            const num = document.createElement('div');
            num.className = 'el-num';
            num.textContent = i;

            const time = document.createElement('input');
            time.type = 'text';
            time.id = prefix + 'time' + i;
            time.name = 'rows[' + i + '][time]';
            time.className = 'input';
            time.placeholder = '0:00';
            time.inputMode = 'numeric';
            time.dataset.mask = '_:__';
            time.value = data.time || '';
            applyMask(time);

            const code = makeSelect(prefix + 'code' + i);
            code.name = 'rows[' + i + '][code]';
            code.value = data.code || '';

            const notes = document.createElement('input');
            notes.type = 'text';
            notes.id = prefix + 'notes' + i;
            notes.name = 'rows[' + i + '][notes]';
            notes.className = 'input';
            notes.placeholder = 'Optional notes…';
            notes.autocomplete = 'off';
            notes.value = data.notes || '';

            row.append(num, time, code, notes);
            container.appendChild(row);
        }
    }

    function gv(id) {
        const el = document.getElementById(id);
        return el ? el.value : '';
    }

    function cv(targetId, sourceId) {
        const target = document.getElementById(targetId);
        const source = document.getElementById(sourceId);
        if (target && source) {
            target.textContent = source.value;
        }
    }

    function sanitizeFilename() {
        return (
            gv('sp_competitor') + '_' +
            gv('sp_category') + '_' +
            config.fileSuffix + '_contents'
        ).replace(/[\/:\\*?"<>|& ]/g, '_') || 'ContentSheet';
    }

    function syncHiddenValues() {
        ['competitor', 'category', 'fedtype', 'federation', 'music'].forEach(function (key) {
            cv('sc_' + key, 'sp_' + key);
        });
        cv('sc_representing', 'representing-hidden');
        for (let i = 1; i <= config.rowCount; i++) {
            ['time', 'code', 'notes'].forEach(function (key) {
                cv('sc_' + key + i, 'sp_' + key + i);
            });
        }
    }

    function generatePDF() {
        if (!window.jspdf || !window.jspdf.jsPDF || typeof window.jspdf.jsPDF !== 'function') {
            alert('No se pudo cargar la libreria PDF del navegador.');
            return;
        }

        syncHiddenValues();

        const { jsPDF } = window.jspdf;
        const doc = new jsPDF({ unit: 'mm', format: 'a4', orientation: 'portrait', compress: true });
        const NAVY = BLUE.pdf.hdr;
        const BLUE2 = BLUE.pdf.col;
        const m = 10;
        const W = 210;
        const cW = W - m * 2;
        let y = 10;

        function hdrBand(txt, top) {
            doc.setFillColor(...NAVY);
            doc.roundedRect(m, top, cW, 11, 2, 2, 'F');
            let tx = m + 4;
            if (logoData) {
                try {
                    const lh = 9;
                    const lw = lh * logoAspect;
                    doc.addImage(logoData, logoType, m + 2, top + 1, lw, lh);
                    tx = m + lw + 4;
                } catch (e) {}
            }
            doc.setFont('helvetica', 'bold');
            doc.setFontSize(13);
            doc.setTextColor(255, 255, 255);
            doc.text(txt, tx, top + 7.5);
            doc.setTextColor(0, 0, 0);
            return top + 13;
        }

        function elemTable(startY, count) {
            const rows = [];
            for (let i = 1; i <= count; i++) {
                rows.push([
                    String(i),
                    gv('sp_time' + i),
                    gv('sp_code' + i),
                    '',
                    gv('sp_notes' + i)
                ]);
            }

            doc.autoTable({
                startY: startY,
                margin: { left: m, right: m },
                tableWidth: cW,
                head: [[
                    { content: '#', styles: { halign: 'center', fontStyle: 'bold', fillColor: BLUE2, textColor: 255 } },
                    { content: 'Time', styles: { fontStyle: 'bold', fillColor: BLUE2, textColor: 255 } },
                    { content: 'Code', styles: { fontStyle: 'bold', fillColor: BLUE2, textColor: 255 } },
                    { content: '(Tech Panel)', styles: { fontStyle: 'bold', fillColor: BLUE2, textColor: 255 } },
                    { content: 'Notes', styles: { fontStyle: 'bold', fillColor: BLUE2, textColor: 255 } }
                ]],
                body: rows,
                theme: 'grid',
                headStyles: { fontSize: 9 },
                bodyStyles: { fontSize: 12, minCellHeight: 25, valign: 'middle' },
                alternateRowStyles: { fillColor: [248, 250, 252] },
                columnStyles: {
                    0: { cellWidth: 12, halign: 'center' },
                    1: { cellWidth: 22 },
                    2: { cellWidth: 24 },
                    4: { cellWidth: 52 }
                }
            });

            return doc.lastAutoTable.finalY + 5;
        }

        y = hdrBand(config.title, y);

        doc.setFont('helvetica', 'bold');
        doc.setFontSize(9);
        doc.text('NAME:', m, y + 4);
        doc.setFont('helvetica', 'normal');
        doc.text(gv('sp_competitor'), m + 13, y + 4);
        doc.setDrawColor(180);
        doc.line(m + 13, y + 4.5, m + 90, y + 4.5);

        doc.setFont('helvetica', 'bold');
        doc.text('CATEGORY:', m + 95, y + 4);
        doc.setFont('helvetica', 'normal');
        doc.text(gv('sp_category'), m + 118, y + 4);
        doc.line(m + 118, y + 4.5, W - m, y + 4.5);
        doc.setDrawColor(0);
        y += 8;

        doc.setFont('helvetica', 'bold');
        doc.text(gv('sp_fedtype').toUpperCase() + ':', m, y + 4);
        doc.setFont('helvetica', 'normal');
        doc.text(gv('sp_federation'), m + 26, y + 4);
        doc.setDrawColor(180);
        doc.line(m + 26, y + 4.5, m + 100, y + 4.5);
        doc.setDrawColor(0);
        y += 8;

        doc.setFont('helvetica', 'bold');
        doc.text('MUSIC:', m, y + 4);
        doc.setFont('helvetica', 'normal');
        doc.text(gv('sp_music'), m + 16, y + 4);
        doc.setDrawColor(180);
        doc.line(m + 16, y + 4.5, W - m, y + 4.5);
        doc.setDrawColor(0);
        y += 8;

        elemTable(y, config.rowCount);
        doc.save(sanitizeFilename() + '.pdf');
    }

    function saveTextAsFile() {
        const fields = [
            'sp_competitor', 'sp_category', 'sp_fedtype', 'sp_federation', 'sp_music',
            ...Array.from({ length: config.rowCount }, function (_, i) {
                return ['sp_time', 'sp_code', 'sp_notes'].map(function (prefix) {
                    return prefix + (i + 1);
                });
            }).flat()
        ];

        const blob = new Blob([fields.map(function (id) {
            return id + '=' + gv(id);
        }).join('\r\n')], { type: 'text/plain' });

        const a = document.createElement('a');
        a.download = sanitizeFilename() + '.txt';
        a.href = URL.createObjectURL(blob);
        a.style.display = 'none';
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
    }

    function restoreContents() {
        const file = restoreFile.files[0];
        if (!file) {
            return;
        }

        const reader = new FileReader();
        reader.onload = function (event) {
            const text = String(event.target.result || '');
            text.split(/[\r\n]+/g).forEach(function (line) {
                const idx = line.indexOf('=');
                if (idx < 0) {
                    return;
                }
                const el = document.getElementById(line.slice(0, idx));
                if (el) {
                    el.value = line.slice(idx + 1);
                }
            });

            if (representingHidden && federationField) {
                representingHidden.value = federationField.value;
            }
        };
        reader.readAsText(file);
    }

    buildRows(rowsContainer, 'sp_', config.rowCount, initialRows);

    if (competitorField && categoryField && fedtypeField && federationField && musicField && representingHidden) {
        const syncRepresenting = function () {
            representingHidden.value = federationField.value;
        };
        federationField.addEventListener('input', syncRepresenting);
        federationField.addEventListener('change', syncRepresenting);
        syncRepresenting();
    }

    downloadButton.addEventListener('click', generatePDF);
    saveButton.addEventListener('click', saveTextAsFile);
    restoreButton.addEventListener('click', function () {
        restoreFile.click();
    });
    restoreFile.addEventListener('change', restoreContents);

    if (window.navigator.userAgent.indexOf('Edg/') !== -1) {
        const edgeNotice = document.createElement('div');
        edgeNotice.className = 'alert';
        edgeNotice.textContent = 'Microsoft Edge puede mostrar advertencias de descarga al generar el PDF.';
        form.parentNode.insertBefore(edgeNotice, form);
    }
})();
</script>
