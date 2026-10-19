/* ==========================================================================
   case-status-chart.js (v2)  ->  simpan di:  public/js/case-status-chart.js
   Dipakai bersama oleh halaman login & partial tat-over5-chart (Dashboard
   CE / Semarang / Cirebon / Master). Isinya:
     - badge ikon + pill status di judul kartu  (Pending = jam pasir, Close = centang)
     - tatShare(): grafik TAT > 5 hari (batang IW & OW per tanggal, tiap batang
       dipecah "<= 5 hari" vs "> 5 hari" beserta persen)  [v2]
     - tatNote():  ringkasan di bawah grafik TAT                    [v2]
   Kartu ditandai dengan atribut:  data-case-status="pending"  atau  "close"
   ========================================================================== */
(function(w, d) {
    'use strict';

    var STATUS = {
        pending: { color: '#F08C00', label: 'Masih Pending', tip: '\u23F3 Status: masih pending (belum selesai diperbaiki)' },
        close: { color: '#1E8A4C', label: 'Sudah Close', tip: '\u2714 Status: sudah close (selesai diperbaiki)' },
        // Part Ready: datanya dari pending case, jadi warnanya sama dengan Pending
        partready: { color: '#F08C00', label: 'Part Tersedia', tip: '\uD83D\uDCE6 Status: part sudah ready, case masih pending' }
    };

    // path ikon (grid 24x24, gaya Lucide)
    var ICON = {
        pending: ['M5 22h14', 'M5 2h14',
            'M17 22v-4.172a2 2 0 0 0-.586-1.414L12 12l-4.414 4.414A2 2 0 0 0 7 17.828V22',
            'M7 2v4.172a2 2 0 0 0 .586 1.414L12 12l4.414-4.414A2 2 0 0 0 17 6.172V2'
        ],
        close: ['M20 6 9 17l-5-5'],
        // kotak paket + centang (part/sparepart siap)
        partready: ['m16 16 2 2 4-4',
            'M21 10V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l2-1.14',
            'M7.5 4.27l9 5.15', 'M3.29 7 12 12l8.71-5', 'M12 22V12']
    };

    function svg(status) {
        return '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round">' +
            ICON[status].map(function(p) { return '<path d="' + p + '"/>'; }).join('') + '</svg>';
    }

    // ---------------- CSS ----------------
    var css = '' +
        '.csc-card{border-left:4px solid var(--csc);overflow:hidden}' +
        '.csc-card--pending,.csc-card--partready{--csc:#F08C00;--csc-soft:#FFF3DC;--csc-glow:240,140,0}' +
        '.csc-card--close{--csc:#1E8A4C;--csc-soft:#E3F5EA;--csc-glow:30,138,76}' +
        '.csc-title{display:flex!important;align-items:center;gap:10px;flex-wrap:wrap}' +
        '.csc-badge{width:34px;height:34px;flex:none;border-radius:50%;display:inline-grid;place-items:center;background:var(--csc-soft);color:var(--csc)}' +
        '.csc-badge svg{width:19px;height:19px}' +
        '.csc-card--pending .csc-badge svg{animation:cscFlip 3.4s ease-in-out infinite}' +
        '.csc-card--close .csc-badge svg path{stroke-dasharray:30;stroke-dashoffset:30;animation:cscDraw .9s .3s ease forwards}' +
        '.csc-pill{display:inline-flex;align-items:center;gap:6px;font-size:11.5px;font-weight:700;letter-spacing:.02em;padding:3px 10px;border-radius:999px;background:var(--csc-soft);color:var(--csc)}' +
        '.csc-dot{width:7px;height:7px;border-radius:50%;background:var(--csc)}' +
        '.csc-card--pending .csc-dot,.csc-card--partready .csc-dot{animation:cscPulse 1.6s ease-out infinite}' +
        '.csc-card--partready .csc-badge svg{animation:cscBob 2.4s ease-in-out infinite}' +
        '@keyframes cscBob{0%,100%{transform:translateY(1px)}50%{transform:translateY(-2px)}}' +
        '.csc-num{font-size:17px;font-weight:800;color:var(--csc,#10222F)}' +
        '@keyframes cscFlip{0%,55%{transform:rotate(0)}80%,100%{transform:rotate(180deg)}}' +
        '@keyframes cscDraw{to{stroke-dashoffset:0}}' +
        '@keyframes cscPulse{0%{box-shadow:0 0 0 0 rgba(var(--csc-glow),.55)}100%{box-shadow:0 0 0 8px rgba(var(--csc-glow),0)}}';
    var st = d.createElement('style');
    st.textContent = css;
    d.head.appendChild(st);

    // ---------------- badge + pill di judul kartu ----------------
    function decorate(root) {
        (root || d).querySelectorAll('[data-case-status]').forEach(function(card) {
            if (card.dataset.cscDone) return;
            var key = card.getAttribute('data-case-status');
            var s = STATUS[key];
            var h = card.querySelector('.chart-card__head h2, .panel__title');
            if (!s || !h) return;

            card.classList.add('csc-card', 'csc-card--' + key);
            h.classList.add('csc-title');

            var badge = d.createElement('span');
            badge.className = 'csc-badge';
            badge.innerHTML = svg(key);

            var pill = d.createElement('span');
            pill.className = 'csc-pill';
            pill.innerHTML = '<i class="csc-dot"></i>' + s.label;

            h.insertBefore(badge, h.firstChild);
            h.appendChild(pill);
            card.dataset.cscDone = '1';
        });
    }

    // ---------------- plugin Chart.js: total + naik/turun (%) ----------------
    // Tanpa ikon/penanda di atas batang supaya label persentase di dalam
    // batang tidak tertutup. Status cukup ditandai di judul kartu & tooltip.
    function totalDeltaPlugin(totals, lineIdx) {
        return {
            id: 'cscTotalDelta',
            afterDatasetsDraw: function(chart) {
                var ctx = chart.ctx;
                ctx.save();
                ctx.textAlign = 'center';
                chart.getDatasetMeta(lineIdx).data.forEach(function(pt, i) {
                    var total = totals[i] || 0;
                    ctx.font = "700 13px 'IBM Plex Sans', system-ui, sans-serif";
                    ctx.fillStyle = '#10222F';
                    ctx.fillText(String(total), pt.x, pt.y - 26);

                    if (i > 0) {
                        var prev = totals[i - 1] || 0,
                            txt = '',
                            col = '#8a97a3';
                        if (prev > 0) {
                            var pct = Math.round(((total - prev) / prev) * 100);
                            if (pct > 0) {
                                txt = '\u25B2 ' + pct + '%';
                                col = '#1E8A4C';
                            } else if (pct < 0) {
                                txt = '\u25BC ' + Math.abs(pct) + '%';
                                col = '#C23B3B';
                            } else { txt = '\u2013 0%'; }
                        } else if (total > 0) {
                            txt = 'baru';
                            col = '#1E8A4C';
                        }

                        if (txt) {
                            ctx.font = "700 11px 'IBM Plex Sans', system-ui, sans-serif";
                            ctx.fillStyle = col;
                            ctx.fillText(txt, pt.x, pt.y - 12);
                        }
                    }
                });
                ctx.restore();
            }
        };
    }

    // footer tooltip berisi status case
    function tooltip(status) {
        var s = STATUS[status];
        return s ? { callbacks: { footer: function() { return s.tip; } } } : {};
    }

    // angka ringkasan (menghitung naik) + teks penjelas
    function setNote(el, n, rest, status) {
        if (!el) return;
        n = Number(n) || 0;
        el.textContent = '';
        var num = d.createElement('strong');
        num.className = 'csc-num';
        num.textContent = '0';
        el.appendChild(num);
        el.appendChild(d.createTextNode(' ' + rest));

        if (!n) return;
        var t0 = null;
        (function step(ts) {
            t0 = t0 || ts;
            var p = Math.min(1, (ts - t0) / 900);
            num.textContent = Math.round(n * (1 - Math.pow(1 - p, 3)));
            if (p < 1) w.requestAnimationFrame(step);
        })(performance.now());
        // (t0 diisi frame pertama; performance.now() hanya pemicu awal)
    }


    // ---------------- grafik TAT: IW & OW per tanggal, dipecah <= N hari vs > N hari ----------------
    var TAT_COLOR = { within: '#7FA3C7', over: '#D64545' };
    // garis tren total per IW / OW (beda warna dari batang & beda satu sama lain)
    var TAT_LINE = { iw: '#1F5FBF', ow: '#F08C00' };
    var TAT_FONT = "'IBM Plex Sans', system-ui, sans-serif";

    /**
     * cfg = {
     *   dateLabels: ['27 Sep', ...], minTat: 5, status: 'pending' | 'close',
     *   iw: { over: [...], within: [...] },   // satu angka per tanggal
     *   ow: { over: [...], within: [...] }
     * }
     * Tiap tanggal = 2 batang (IW, OW). Segmen atas = TAT > minTat (merah),
     * segmen bawah = TAT <= minTat. Label segmen: "jumlah (persen%)", persen
     * dihitung dari total batang itu. Angka di atas batang = total batang.
     */
    function tatShare(canvasId, cfg) {
        var el = d.getElementById(canvasId);
        if (!el || typeof Chart === 'undefined') return null;

        var min = cfg.minTat;
        var labels = [],
            within = [],
            over = [],
            info = [];

        cfg.dateLabels.forEach(function(lab, g) {
            [
                ['IW', cfg.iw],
                ['OW', cfg.ow]
            ].forEach(function(p) {
                labels.push(p[0]);
                within.push(p[1].within[g] || 0);
                over.push(p[1].over[g] || 0);
                info.push({ w: p[0], date: lab });
            });
            if (g < cfg.dateLabels.length - 1) { // celah antar tanggal
                labels.push('');
                within.push(null);
                over.push(null);
                info.push(null);
            }
        });

        function pct(v, t) { return t ? Math.round((v / t) * 100) : 0; }

        // total batang per posisi; garis IW / OW hanya punya nilai di posisi batangnya sendiri
        var totals = within.map(function(v, i) { return info[i] ? v + over[i] : null; });
        var lineIw = totals.map(function(t, i) { return info[i] && info[i].w === 'IW' ? t : null; });
        var lineOw = totals.map(function(t, i) { return info[i] && info[i].w === 'OW' ? t : null; });

        var barsPlugin = {
            id: 'cscTatBars',
            afterDatasetsDraw: function(chart) {
                var ctx = chart.ctx;
                var mW = chart.getDatasetMeta(0),
                    mO = chart.getDatasetMeta(1);
                ctx.save();
                ctx.textAlign = 'center';

                info.forEach(function(inf, i) {
                    if (!inf) return;
                    var eW = mW.data[i],
                        eO = mO.data[i];
                    var t = within[i] + over[i];

                    // label di dalam segmen: "jumlah (persen%)" satu baris; kalau batang terlalu sempit
                    // jadi 2 baris; kalau segmen terlalu pendek, tidak digambar di dalam
                    function inside(e, v) {
                        if (!v) return false;
                        var h = Math.abs(e.base - e.y),
                            cy = (e.y + e.base) / 2;
                        var full = v + ' (' + pct(v, t) + '%)';
                        ctx.font = '700 10.5px ' + TAT_FONT;
                        ctx.fillStyle = '#fff';
                        if (h >= 15 && ctx.measureText(full).width <= e.width - 4) {
                            ctx.fillText(full, e.x, cy + 4);
                            return true;
                        }
                        if (h >= 28) {
                            ctx.fillText(String(v), e.x, cy - 1);
                            ctx.fillText('(' + pct(v, t) + '%)', e.x, cy + 11);
                            return true;
                        }
                        return false;
                    }
                    inside(eW, within[i]);
                    var overShown = inside(eO, over[i]);

                    // total batang di atas + naik/turun (%) dibanding tanggal sebelumnya (IW vs IW, OW vs OW)
                    var top = Math.min(eW.y, eO.y);
                    ctx.font = '700 13px ' + TAT_FONT;
                    ctx.fillStyle = '#10222F';
                    ctx.fillText(String(t), eW.x, top - 24);

                    var hasPrev = i >= 3 && info[i - 3];
                    if (hasPrev) {
                        var prev = totals[i - 3] || 0, txt = '', col = '#8a97a3';
                        if (prev > 0) {
                            var dp = Math.round(((t - prev) / prev) * 100);
                            if (dp > 0)      { txt = '\u25B2 ' + dp + '%'; col = '#1E8A4C'; }
                            else if (dp < 0) { txt = '\u25BC ' + Math.abs(dp) + '%'; col = '#C23B3B'; }
                            else             { txt = '\u2013 0%'; }
                        } else if (t > 0) { txt = 'baru'; col = '#1E8A4C'; }
                        if (txt) {
                            ctx.font = '700 11px ' + TAT_FONT;
                            ctx.fillStyle = col;
                            ctx.fillText(txt, eW.x, top - 11);
                        }
                    }

                    // segmen "> N hari" (merah) terlalu pendek untuk label: tulis di atas total, warna merah
                    if (over[i] && !overShown) {
                        ctx.font = '700 11px ' + TAT_FONT;
                        ctx.fillStyle = TAT_COLOR.over;
                        ctx.fillText(over[i] + ' (' + pct(over[i], t) + '%)', eW.x, top - 40);
                    }
                });
                ctx.restore();
            },
            // tanggal di bawah pasangan IW | OW
            afterDraw: function(chart) {
                var xs = chart.scales.x,
                    ctx = chart.ctx;
                ctx.save();
                ctx.textAlign = 'center';
                ctx.font = '700 12px ' + TAT_FONT;
                ctx.fillStyle = '#41525F';
                cfg.dateLabels.forEach(function(lab, g) {
                    var x = (xs.getPixelForTick(g * 3) + xs.getPixelForTick(g * 3 + 1)) / 2;
                    ctx.fillText(lab, x, xs.bottom - 6);
                });
                ctx.restore();
            }
        };

        var st = STATUS[cfg.status];

        return new Chart(el, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [
                    { label: '\u2264 ' + min + ' hari', data: within, backgroundColor: TAT_COLOR.within, stack: 'tat', maxBarThickness: 64, order: 2 },
                    { label: '> ' + min + ' hari', data: over, backgroundColor: TAT_COLOR.over, stack: 'tat', maxBarThickness: 64, order: 2 },
                    { type: 'line', label: 'Tren IW', data: lineIw, stack: 'lineIw', order: 1, spanGaps: true,
                      borderColor: TAT_LINE.iw, backgroundColor: TAT_LINE.iw, borderWidth: 3, tension: 0,
                      pointRadius: 5, pointHoverRadius: 6, pointBackgroundColor: TAT_LINE.iw, pointBorderColor: '#fff', pointBorderWidth: 2, fill: false },
                    { type: 'line', label: 'Tren OW', data: lineOw, stack: 'lineOw', order: 1, spanGaps: true,
                      borderColor: TAT_LINE.ow, backgroundColor: TAT_LINE.ow, borderWidth: 3, tension: 0,
                      pointRadius: 5, pointHoverRadius: 6, pointBackgroundColor: TAT_LINE.ow, pointBorderColor: '#fff', pointBorderWidth: 2, fill: false }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                animation: { duration: 900, easing: 'easeOutQuart' },
                layout: { padding: { top: 58 } },
                interaction: { mode: 'index', intersect: false },
                plugins: {
                    datalabels: { display: false },
                    legend: { position: 'bottom', labels: { sort: function(a, b) { return a.datasetIndex - b.datasetIndex; } } },
                    tooltip: {
                        filter: function(it) { return it.datasetIndex < 2; }, // garis tren tidak ikut di tooltip
                        callbacks: {
                            title: function(items) {
                                var inf = info[items[0].dataIndex];
                                return inf ? inf.date + ' \u00B7 ' + inf.w : '';
                            },
                            label: function(c) {
                                var i = c.dataIndex,
                                    t = within[i] + over[i],
                                    v = c.parsed.y || 0;
                                return ' ' + c.dataset.label + ': ' + v + ' case (' + pct(v, t) + '%)';
                            },
                            footer: function(items) {
                                var i = items[0].dataIndex;
                                var f = ['Total: ' + (within[i] + over[i]) + ' case'];
                                if (st) f.push(st.tip);
                                return f;
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        stacked: true,
                        grid: { display: false },
                        ticks: { autoSkip: false, font: { weight: '600' } },
                        afterFit: function(scale) { scale.height += 24; } // ruang untuk tanggal
                    },
                    y: {
                        stacked: true,
                        beginAtZero: true,
                        grace: '15%',
                        ticks: { precision: 0 },
                        grid: { color: '#E1E8EF' },
                        border: { display: false }
                    }
                }
            },
            plugins: [barsPlugin]
        });
    }

    // ringkasan di bawah grafik TAT: "N dari M case (P%) TAT > 5 hari ... - IW a% - OW b%"
    // opt = { latest: true -> hanya tanggal terakhir (stok pending), false -> jumlah semua tanggal;
    //         noun: 'Pending Case' | 'Close Case' }
    function tatNote(el, cfg, opt) {
        if (!el) return;
        var n = cfg.dateLabels.length;
        var idx = opt.latest ? [n - 1] : cfg.dateLabels.map(function(_, i) { return i; });

        function sum(arr) { return idx.reduce(function(a, i) { return a + ((arr && arr[i]) || 0); }, 0); }

        function pct(a, b) { return b ? Math.round((a / b) * 100) + '%' : '\u2013'; }

        var oi = sum(cfg.iw.over),
            wi = sum(cfg.iw.within),
            oo = sum(cfg.ow.over),
            wo = sum(cfg.ow.within);
        var over = oi + oo,
            all = over + wi + wo;
        var when = opt.latest ? 'per ' + cfg.dateLabels[n - 1] : 'sepanjang ' + n + ' hari yang tampil';

        setNote(el, over,
            'dari ' + all + ' ' + opt.noun + ' (' + pct(over, all) + ') TAT > ' + cfg.minTat + ' hari ' + when +
            ' \u00B7 IW ' + pct(oi, oi + wi) + ' \u00B7 OW ' + pct(oo, oo + wo),
            cfg.status);
    }

    w.CaseStatusChart = { decorate: decorate, totalDeltaPlugin: totalDeltaPlugin, tooltip: tooltip, setNote: setNote, tatShare: tatShare, tatNote: tatNote, STATUS: STATUS };

    if (d.readyState === 'loading') d.addEventListener('DOMContentLoaded', function() { decorate(); });
    else decorate();
})(window, document);