<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Informe Técnico — Evidencias</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Archivo:wght@500;600;700;800&family=Manrope:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.js"></script>
<style>
:root{
  --bg:#eef2f7;
  --surface:#ffffff;
  --ink:#16263f;
  --ink-soft:#5a6b80;
  --ink-faint:#97a3b3;
  --line:#dde4ec;
  --accent:#1f6fc4;
  --accent-soft:#e7f0fb;
  --accent-ink:#155293;
  --ok:#2f9e44;
  --danger:#e03131;
  --danger-soft:#ffeaea;
  --radius:14px;
  --shadow:0 1px 2px rgba(22,38,63,.05), 0 8px 24px rgba(22,38,63,.07);
}
*{box-sizing:border-box;margin:0;padding:0;-webkit-tap-highlight-color:transparent}
html,body{background:var(--bg);color:var(--ink);font-family:'Manrope',system-ui,sans-serif;line-height:1.5;-webkit-font-smoothing:antialiased}
body{padding-bottom:120px}

/* ---------- Header ---------- */
.hero{
  position:relative;
  overflow:hidden;
  text-align:center;
  color:#fff;
  min-height:430px;
  padding:70px 20px 98px;
  display:flex;
  align-items:center;
  justify-content:center;
  background:
    linear-gradient(180deg,rgba(6,16,31,.56) 0%,rgba(8,22,41,.62) 52%,rgba(8,18,34,.92) 100%),
    radial-gradient(circle at 50% 10%,rgba(31,111,196,.34),transparent 44%),
    url('zgroup-tec.jpg') center 46%/cover no-repeat;
}
.hero::before{
  content:"";
  position:absolute;
  inset:0;
  pointer-events:none;
  background:
    linear-gradient(90deg,rgba(3,12,25,.78) 0%,rgba(3,12,25,.24) 28%,rgba(3,12,25,.24) 72%,rgba(3,12,25,.78) 100%),
    radial-gradient(circle at 50% 58%,transparent 0%,transparent 34%,rgba(0,0,0,.30) 100%);
}
.hero::after{
  content:"";
  position:absolute;
  left:0;
  right:0;
  bottom:-1px;
  height:115px;
  pointer-events:none;
  background:linear-gradient(0deg,var(--bg) 0%,rgba(238,242,247,.86) 34%,rgba(238,242,247,.22) 72%,transparent 100%);
}
.clock{position:absolute;top:16px;right:18px;z-index:3;display:flex;flex-direction:column;align-items:flex-end;color:#fff;line-height:1.1;background:rgba(8,20,38,.38);border:1px solid rgba(255,255,255,.28);padding:9px 14px;border-radius:13px;backdrop-filter:blur(10px);box-shadow:0 12px 28px rgba(0,0,0,.20)}
.clock .time{font-family:'Archivo',sans-serif;font-weight:800;font-size:19px;letter-spacing:.03em;font-variant-numeric:tabular-nums}
.clock .date{font-size:10px;color:#d9e7f7;font-weight:600;margin-top:2px}
.hero-inner{position:relative;z-index:2;max-width:900px;margin:0 auto;display:flex;flex-direction:column;align-items:center;width:100%}
.hero-copy{display:flex;flex-direction:column;align-items:center;min-width:0;width:100%}
.brand-plate{background:rgba(255,255,255,.96);border:1px solid rgba(255,255,255,.75);border-radius:20px;padding:16px 30px;box-shadow:0 20px 46px rgba(0,0,0,.34);display:inline-flex;margin-bottom:18px;backdrop-filter:blur(10px)}
.brand-plate img{height:58px;width:auto;display:block}
.kicker{display:inline-flex;align-items:center;gap:8px;font-size:11px;font-weight:900;letter-spacing:.18em;text-transform:uppercase;color:#eaf5ff;background:rgba(31,111,196,.34);border:1px solid rgba(157,207,255,.55);padding:7px 14px;border-radius:999px;backdrop-filter:blur(10px);margin-bottom:14px;box-shadow:0 9px 24px rgba(31,111,196,.18)}
.kicker .dot{width:8px;height:8px;border-radius:50%;background:#8fc0f2;box-shadow:0 0 0 5px rgba(143,192,242,.18)}
.hero h1{font-family:'Archivo',sans-serif;font-weight:800;font-size:clamp(36px,7vw,62px);line-height:.94;margin:0 0 12px;letter-spacing:-.04em;text-shadow:0 15px 32px rgba(0,0,0,.42)}
.hero-sub{max-width:710px;color:#f0f7ff;font-size:16px;font-weight:650;line-height:1.55;margin-bottom:20px;text-shadow:0 5px 16px rgba(0,0,0,.42)}
.hero-pills{display:flex;flex-wrap:wrap;gap:10px;justify-content:center}
.hero-pill{display:inline-flex;align-items:center;gap:7px;background:rgba(9,23,42,.42);border:1px solid rgba(255,255,255,.28);color:#fff;border-radius:999px;padding:9px 14px;font-weight:900;font-size:12px;backdrop-filter:blur(10px);box-shadow:0 12px 28px rgba(0,0,0,.18)}
.hero-panel{display:none}
.ops-strip{display:grid;grid-template-columns:repeat(3,1fr);gap:13px;margin-bottom:18px;margin-top:-58px;position:relative;z-index:3}
.ops-strip.no-overlap{margin-top:0;margin-bottom:22px}
.ops-mini{background:linear-gradient(180deg,rgba(255,255,255,.97),#f7fafd);border:1px solid rgba(221,228,236,.92);border-radius:18px;box-shadow:0 14px 34px rgba(22,38,63,.12);padding:16px 16px;display:flex;gap:12px;align-items:center}
.ops-icon{flex:none;width:42px;height:42px;border-radius:15px;background:var(--accent-soft);display:grid;place-items:center;font-size:20px}
.ops-mini strong{display:block;font-family:'Archivo';font-size:14px;color:var(--ink);line-height:1.15}
.ops-mini span{font-size:11.8px;color:var(--ink-soft);font-weight:650}
@media(max-width:860px){
  .hero{min-height:420px;padding:76px 16px 92px;background-position:center center}
  .hero-sub{text-align:center;font-size:15px}
}
@media(max-width:620px){
  .clock{position:relative;top:auto;right:auto;margin:0 auto 18px;width:max-content;align-items:center}
  .hero{padding:22px 16px 86px;min-height:390px}
  .brand-plate{padding:12px 20px;border-radius:16px}
  .brand-plate img{height:44px}
  .hero h1{font-size:38px}
  .hero-pills{gap:8px}
  .hero-pill{font-size:11px;padding:8px 11px}
  .ops-strip{grid-template-columns:1fr;margin-top:-42px}
}

/* ---------- Layout ---------- */
.wrap{max-width:880px;margin:0 auto;padding:20px 16px 0}
.card{background:var(--surface);border:1px solid var(--line);border-radius:var(--radius);box-shadow:var(--shadow);padding:20px;margin-bottom:18px}
.card-head{display:flex;align-items:center;gap:12px;margin-bottom:18px}
.step{flex:none;width:30px;height:30px;border-radius:9px;background:var(--ink);color:#fff;font-family:'Archivo';font-weight:800;font-size:14px;display:grid;place-items:center}
.card-head h2{font-family:'Archivo',sans-serif;font-weight:700;font-size:18px;letter-spacing:-.01em}
.card-head .sub{font-size:12.5px;color:var(--ink-soft);font-weight:500;margin-top:1px}

/* ---------- Datos generales desplegable cuando ya existe preliminar ---------- */
.datos-generales-card{overflow:hidden;transition:box-shadow .18s,border-color .18s}
.datos-generales-card.datos-collapsed{padding-bottom:18px}
.datos-toggle{width:100%;border:0;background:transparent;padding:0;margin:0;display:flex;align-items:center;justify-content:space-between;gap:12px;text-align:left;cursor:pointer;color:inherit}
.datos-toggle .card-head{margin-bottom:0;flex:1}
.datos-toggle:hover h2{color:var(--accent-ink)}
.datos-side{display:flex;align-items:center;gap:10px;flex:none}
.datos-pill{font-family:'Archivo';font-size:12px;font-weight:800;color:var(--accent-ink);background:var(--accent-soft);border:1px solid #cfe0f4;border-radius:999px;padding:8px 12px;white-space:nowrap}
.datos-arrow{width:34px;height:34px;border-radius:12px;background:var(--ink);color:#fff;display:grid;place-items:center;font-family:'Archivo';font-weight:900;transition:transform .18s,background .18s}
.datos-generales-card:not(.datos-collapsed) .datos-arrow{transform:rotate(180deg);background:var(--accent)}
.datos-body{margin-top:18px;overflow:hidden;transition:max-height .22s ease,opacity .18s ease}
.datos-generales-card.datos-collapsed .datos-body{max-height:0!important;opacity:0;margin-top:0;pointer-events:none}
.datos-generales-card:not(.datos-collapsed) .datos-body{max-height:none;opacity:1}
@media(max-width:620px){.datos-toggle{align-items:flex-start}.datos-side{flex-direction:column;gap:7px;align-items:flex-end}.datos-pill{font-size:11px;padding:7px 10px}.datos-arrow{width:30px;height:30px;border-radius:10px}}

/* ---------- Forms ---------- */
.grid2{display:grid;grid-template-columns:1fr 1fr;gap:14px}
.field{display:flex;flex-direction:column;gap:6px}
.field.full{grid-column:1/-1}
label{font-size:12px;font-weight:700;color:var(--ink-soft);letter-spacing:.02em}
label .opt{color:var(--ink-faint);font-weight:500;text-transform:none;letter-spacing:0}
input[type=text],input[type=date],textarea,select{
  font-family:inherit;font-size:15px;color:var(--ink);
  background:#f7fafd;border:1.5px solid var(--line);border-radius:10px;
  padding:12px 13px;width:100%;transition:border-color .15s,box-shadow .15s;
}
input:focus,textarea:focus,select:focus{outline:none;border-color:var(--accent);box-shadow:0 0 0 3px var(--accent-soft);background:#fff}
textarea{resize:vertical;min-height:80px;line-height:1.5}
select{appearance:none;-webkit-appearance:none;background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='14' height='14' viewBox='0 0 24 24' fill='none' stroke='%235b6675' stroke-width='2.5'%3E%3Cpolyline points='6 9 12 15 18 9'/%3E%3C/svg%3E");background-repeat:no-repeat;background-position:right 13px center;padding-right:38px;cursor:pointer}
input[type=number]{font-family:inherit;font-size:15px;color:var(--ink);background:#f7fafd;border:1.5px solid var(--line);border-radius:10px;padding:12px 13px;width:100%;transition:border-color .15s,box-shadow .15s}
input[type=number]:focus{outline:none;border-color:var(--accent);box-shadow:0 0 0 3px var(--accent-soft);background:#fff}

.input-error{border-color:var(--danger)!important;background:#fff7f7!important;box-shadow:0 0 0 3px var(--danger-soft)!important}
.field-error{display:none;font-size:12px;color:var(--danger);font-weight:700;margin-top:2px;line-height:1.35}
.field-error.show{display:block}

/* ---------- Buscador de técnico (autocompletar) ---------- */
.autocomplete{position:relative}
.ac-list{position:absolute;left:0;right:0;top:calc(100% + 5px);background:#fff;border:1.5px solid var(--line);border-radius:11px;box-shadow:var(--shadow);max-height:230px;overflow-y:auto;z-index:30;display:none}
.ac-list.show{display:block}
.ac-item{padding:11px 14px;font-size:14.5px;cursor:pointer;border-bottom:1px solid var(--line)}
.ac-item:last-child{border-bottom:none}
.ac-item:hover,.ac-item.active{background:var(--accent-soft);color:var(--accent-ink)}
.ac-empty{padding:11px 14px;font-size:13.5px;color:var(--ink-faint);font-style:italic}
.ac-input.ok{border-color:var(--ok);background:#f0faf3}
.zg-tech-autocomplete{z-index:45}
.zg-tech-source{position:absolute!important;width:1px!important;height:1px!important;opacity:0!important;pointer-events:none!important;left:0!important;bottom:0!important;padding:0!important;border:0!important}
.zg-tech-list{z-index:80;max-height:270px;overscroll-behavior:contain}
.zg-tech-list .ac-item{display:block;width:100%;border:0;border-bottom:1px solid var(--line);background:#fff;text-align:left;font-family:inherit;color:var(--ink)}
.zg-tech-list .ac-item:last-child{border-bottom:0}
.zg-tech-list .ac-item:hover,.zg-tech-list .ac-item.active{background:var(--accent-soft);color:var(--accent-ink)}
@media(max-width:620px){.zg-tech-list{max-height:240px}.zg-tech-list .ac-item{padding:13px 14px}}

/* ---------- Campos por trabajo ---------- */
.campos{display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:14px}
.campos .field.full{grid-column:1/-1}
@media(max-width:520px){.campos{grid-template-columns:1fr}}

/* ---------- Campo de ubicación (abre mapa) ---------- */
.dir-pick{position:relative;cursor:pointer}
.dir-pick svg{position:absolute;left:12px;top:50%;transform:translateY(-50%);width:18px;height:18px;stroke:var(--accent);fill:none;stroke-width:2;pointer-events:none}
.dir-pick input{padding-left:40px!important;cursor:pointer;background:#f7fafd}
.dir-pick:hover input{border-color:var(--accent)}

/* ---------- Modal de mapa ---------- */
.map-modal{position:fixed;inset:0;z-index:200;background:rgba(13,22,38,.55);backdrop-filter:blur(3px);display:none;align-items:center;justify-content:center;padding:14px}
.map-modal.show{display:flex}
.map-box{background:#fff;border-radius:18px;box-shadow:0 24px 70px rgba(0,0,0,.45);width:100%;max-width:680px;height:min(86vh,720px);display:flex;flex-direction:column;overflow:hidden}
.map-head{display:flex;align-items:center;justify-content:space-between;padding:15px 18px;background:var(--ink);color:#fff}
.map-head .map-title{font-family:'Archivo';font-weight:700;font-size:15px}
.map-close{border:none;background:rgba(255,255,255,.12);color:#fff;width:30px;height:30px;border-radius:9px;font-size:20px;line-height:1;cursor:pointer}
.map-close:hover{background:var(--accent)}
.map-search{display:flex;gap:8px;padding:12px 14px;border-bottom:1px solid var(--line);position:relative}
.map-search input{flex:1}
.map-search button{font-family:'Archivo';font-weight:700;font-size:13.5px;background:var(--accent);color:#fff;border:none;border-radius:10px;padding:0 16px;cursor:pointer}
.pick-map{flex:1;min-height:360px;background:#e9eef3}
.map-foot{padding:13px 16px;border-top:1px solid var(--line);display:flex;flex-direction:column;gap:11px}
.map-addr{font-size:13px;color:var(--ink-soft);line-height:1.4;min-height:18px}
.map-addr b{color:var(--ink)}
.map-foot .btn{width:100%}
.cur-dot{position:relative}
.cur-dot .d{position:absolute;left:50%;top:50%;width:15px;height:15px;margin:-7.5px 0 0 -7.5px;background:#1f6fc4;border:3px solid #fff;border-radius:50%;box-shadow:0 0 0 2px rgba(31,111,196,.4),0 2px 8px rgba(0,0,0,.35)}
.cur-dot .r{position:absolute;left:50%;top:50%;width:15px;height:15px;margin:-7.5px 0 0 -7.5px;border-radius:50%;background:rgba(31,111,196,.4);animation:pulse 2s ease-out infinite}
@keyframes pulse{0%{transform:scale(1);opacity:.6}100%{transform:scale(5);opacity:0}}
.sel-pin .p{width:24px;height:24px;background:var(--danger);border:3px solid #fff;border-radius:50% 50% 50% 0;transform:rotate(-45deg);box-shadow:0 4px 9px rgba(0,0,0,.45)}

/* ---------- Buscador de trabajos ---------- */
.work-search{width:100%;margin-bottom:14px}
.work-none{grid-column:1/-1;padding:16px;text-align:center;color:var(--ink-faint);font-size:13.5px;font-style:italic}

/* ---------- Sugerencias del buscador de mapa ---------- */
.map-sug{position:absolute;top:calc(100% - 3px);left:14px;right:14px;background:#fff;border:1.5px solid var(--line);border-radius:12px;box-shadow:0 14px 34px rgba(0,0,0,.2);max-height:320px;overflow-y:auto;z-index:1200;display:none}
.map-sug.show{display:block}
.sug-item{padding:11px 13px;font-size:13px;color:var(--ink);cursor:pointer;border-bottom:1px solid var(--line);line-height:1.35}
.sug-item:last-child{border-bottom:none}
.sug-item:hover{background:var(--accent-soft);color:var(--accent-ink)}
.sug-main{font-weight:800;color:var(--ink);font-size:13.5px}
.sug-sub{font-size:12px;color:var(--ink-soft);margin-top:2px}
.sug-tag{display:inline-flex;align-items:center;gap:4px;margin-top:5px;font-size:11px;color:var(--accent-ink);font-weight:700}
.sug-empty{padding:11px 13px;font-size:12.5px;color:var(--ink-faint);font-style:italic}
.map-route{width:100%;text-decoration:none}

/* ---------- Logo upload ---------- */
.logo-row{display:flex;gap:14px;align-items:center;flex-wrap:wrap}
.logo-box{flex:none;width:96px;height:64px;border:1.5px dashed var(--line);border-radius:10px;display:grid;place-items:center;background:#fbfbf9;cursor:pointer;overflow:hidden;color:var(--ink-faint);font-size:11px;text-align:center;padding:6px}
.logo-box img{max-width:100%;max-height:100%;object-fit:contain}
.logo-box:hover{border-color:var(--accent);color:var(--accent)}

/* ---------- Work type cards ---------- */
.work-grid{display:grid;grid-template-columns:repeat(2,1fr);gap:9px}
@media(min-width:560px){.work-grid{grid-template-columns:repeat(3,1fr)}}
@media(min-width:840px){.work-grid{grid-template-columns:repeat(4,1fr)}}
.work-card{
  position:relative;border:1.5px solid var(--line);border-radius:11px;background:#f7fafd;
  padding:11px 32px 11px 13px;cursor:pointer;transition:all .14s;text-align:left;font-family:inherit;
  display:flex;align-items:center;min-height:56px;
}
.work-card:hover{border-color:#bccddf;background:#fff;transform:translateY(-1px)}
.work-card .nm{font-weight:600;font-size:12.5px;color:var(--ink);line-height:1.22}
.work-card.on{border-color:var(--accent);background:var(--accent-soft);box-shadow:0 0 0 3px rgba(31,111,196,.12)}
.work-card .check{position:absolute;top:50%;right:9px;transform:translateY(-50%);width:19px;height:19px;border-radius:50%;border:1.8px solid var(--line);background:#fff;display:grid;place-items:center;transition:all .14s}
.work-card.on .check{background:var(--accent);border-color:var(--accent)}
.work-card .check svg{width:11px;height:11px;stroke:#fff;stroke-width:3;fill:none;opacity:0;transition:opacity .14s}
.work-card.on .check svg{opacity:1}
.add-work{border-style:dashed;color:var(--ink-soft);justify-content:center;text-align:center;font-weight:700;font-size:13px}
.add-work .ic{font-size:18px;color:var(--accent);margin-right:5px}

/* ---------- Panels (selected work) ---------- */
#panels{margin-top:18px;display:flex;flex-direction:column;gap:16px}
.panel{border:1.5px solid var(--line);border-radius:12px;overflow:hidden;background:#fff;animation:pop .2s ease}
@keyframes pop{from{opacity:0;transform:translateY(6px)}to{opacity:1;transform:none}}
.panel-head{display:flex;align-items:center;gap:10px;padding:13px 15px;background:var(--accent-soft);border-bottom:1.5px solid #cfe1f7}
.panel-head .ic{font-size:18px}
.panel-head .ttl{font-family:'Archivo';font-weight:700;font-size:15.5px;color:var(--accent-ink);flex:1}
.panel-head .rm{flex:none;border:none;background:rgba(0,0,0,.06);color:var(--ink-soft);width:28px;height:28px;border-radius:8px;cursor:pointer;font-size:18px;line-height:1;display:grid;place-items:center}
.panel-head .rm:hover{background:var(--danger-soft);color:var(--danger)}
.panel-body{padding:15px}
.panel-body .field{margin-bottom:14px}

/* ---------- Dropzone + thumbs ---------- */
.drop{display:flex;flex-direction:column;align-items:center;justify-content:center;gap:2px;border:1.5px dashed var(--line);border-radius:11px;background:#f7fafd;padding:20px;text-align:center;cursor:pointer;transition:all .14s;color:var(--ink-soft)}
.drop:hover{border-color:var(--accent);background:var(--accent-soft);color:var(--accent-ink)}
.drop .big{font-weight:700;font-size:14px;color:var(--ink)}
.drop .ic{font-size:26px;margin-bottom:3px}
.drop small{font-size:11.5px;color:var(--ink-faint)}
.thumbs{display:grid;grid-template-columns:repeat(auto-fill,minmax(140px,1fr));gap:12px;margin-top:14px}
.thumb{border:1px solid var(--line);border-radius:10px;overflow:hidden;background:#fff;display:flex;flex-direction:column}
.thumb .ph{position:relative;aspect-ratio:4/3;background:#f0efe9}
.thumb .ph img{width:100%;height:100%;object-fit:cover;display:block}
.thumb .x{position:absolute;top:6px;right:6px;width:26px;height:26px;border-radius:50%;border:none;background:rgba(22,32,46,.72);color:#fff;cursor:pointer;font-size:16px;line-height:1;display:grid;place-items:center;backdrop-filter:blur(2px)}
.thumb .x:hover{background:var(--danger)}
.thumb input{border:none;border-top:1px solid var(--line);border-radius:0;background:#fff;font-size:12.5px;padding:9px 10px}
.thumb input:focus{box-shadow:none;border-top-color:var(--accent)}
.empty-hint{color:var(--ink-faint);font-size:13px;text-align:center;padding:8px 0}

/* ---------- Action bar ---------- */
.actionbar{position:fixed;left:0;right:0;bottom:0;background:rgba(255,255,255,.85);backdrop-filter:blur(12px);border-top:1px solid var(--line);padding:13px 16px;z-index:20}
.actionbar-inner{max-width:880px;margin:0 auto;display:flex;gap:11px;align-items:center}
.btn{font-family:'Archivo',sans-serif;font-weight:700;font-size:15px;border:none;border-radius:11px;padding:14px 18px;cursor:pointer;transition:transform .12s,filter .12s,opacity .12s;display:inline-flex;align-items:center;justify-content:center;gap:8px}
.btn:active{transform:scale(.98)}
.btn-primary{background:var(--accent);color:#fff;flex:1;box-shadow:0 6px 16px rgba(31,111,196,.32)}
.btn-primary:hover{filter:brightness(1.05)}
.btn-primary svg{width:18px;height:18px;stroke:#fff;stroke-width:2.2;fill:none}
.btn-ghost{background:#e6ebf2;color:var(--ink-soft)}
.btn-ghost:hover{background:#d9e0ea}
.count-pill{font-family:'Manrope';font-size:12px;font-weight:600;color:var(--ink-soft)}

/* ---------- Privacy note ---------- */
.privacy{max-width:880px;margin:6px auto 30px;padding:0 16px;text-align:center;color:var(--ink-faint);font-size:12px}
.privacy svg{width:13px;height:13px;vertical-align:-2px;stroke:currentColor;fill:none;stroke-width:2;margin-right:4px}

/* ---------- Overlay ---------- */
.overlay{position:fixed;inset:0;background:rgba(243,242,238,.82);backdrop-filter:blur(4px);display:none;place-items:center;z-index:50}
.overlay.show{display:grid}
.spinner{width:42px;height:42px;border:4px solid var(--accent-soft);border-top-color:var(--accent);border-radius:50%;animation:spin .7s linear infinite;margin:0 auto 14px}
@keyframes spin{to{transform:rotate(360deg)}}
.overlay .msg{font-family:'Archivo';font-weight:700;color:var(--ink);text-align:center}

/* ---------- Toast ---------- */
.toast{position:fixed;bottom:96px;left:50%;transform:translateX(-50%) translateY(20px);background:var(--ink);color:#fff;padding:12px 18px;border-radius:11px;font-size:13.5px;font-weight:600;opacity:0;pointer-events:none;transition:all .25s;z-index:60;box-shadow:0 10px 30px rgba(0,0,0,.25)}
.toast.show{opacity:1;transform:translateX(-50%) translateY(0)}

/* ---------- Confirmación de guardado ---------- */
.savedbox{display:none;margin-top:4px}
.savedbox.show{display:block;animation:pop .25s ease}
.saved-card{display:flex;align-items:center;gap:14px;background:#eaf7ee;border:1.5px solid #b6e3c4;border-radius:14px;padding:16px 18px;margin-bottom:12px}
.saved-ic{flex:none;width:38px;height:38px;border-radius:50%;background:var(--ok);color:#fff;display:grid;place-items:center;font-size:20px;font-weight:800}
.saved-txt{display:flex;flex-direction:column;gap:1px}
.saved-txt strong{font-family:'Archivo';font-size:16px;color:#1c6b34}
.saved-txt span{font-size:13px;color:#3c7a4f}
.saved-actions{display:flex;flex-wrap:wrap;gap:10px}
.saved-actions .btn{flex:1;min-width:150px;text-decoration:none}


/* =========================================================
   OPTIMIZACIÓN MÓVIL PARA TÉCNICOS EN CAMPO
   Pantallas pequeñas, uso con dedo, cámara y carga rápida.
   ========================================================= */
button,input,textarea,select{font-size:16px;touch-action:manipulation}
button,.work-card,.drop,.dir-pick,.btn{touch-action:manipulation}

@media(max-width:780px){
  body{padding-bottom:172px;background:#eef3f8;overflow-x:hidden}
  .hero{
    min-height:380px;
    padding:20px 14px 92px;
    align-items:flex-end;
    background:
      linear-gradient(180deg,rgba(5,15,30,.18) 0%,rgba(7,20,38,.58) 45%,rgba(7,17,32,.96) 100%),
      radial-gradient(circle at 50% 18%,rgba(31,111,196,.25),transparent 42%),
      url('zgroup-tec.jpg') center 38%/cover no-repeat;
  }
  .hero::before{
    background:linear-gradient(90deg,rgba(3,12,25,.40),rgba(3,12,25,.06),rgba(3,12,25,.40));
  }
  .hero::after{height:92px}
  .clock{
    position:absolute;top:12px;right:12px;margin:0;
    padding:7px 10px;border-radius:12px;align-items:flex-end;
    background:rgba(8,20,38,.48);
  }
  .clock .time{font-size:16px}
  .clock .date{font-size:9px}
  .hero-inner{max-width:100%;align-items:flex-start;text-align:left}
  .hero-copy{align-items:flex-start;text-align:left}
  .brand-plate{padding:10px 16px;border-radius:17px;margin-bottom:12px;box-shadow:0 16px 34px rgba(0,0,0,.34)}
  .brand-plate img{height:39px;max-width:220px}
  .kicker{font-size:10px;padding:6px 10px;margin-bottom:9px;letter-spacing:.13em}
  .hero h1{font-size:38px;line-height:.96;margin-bottom:9px;letter-spacing:-.035em;max-width:310px}
  .hero-sub{font-size:14px;line-height:1.42;margin-bottom:13px;max-width:330px;text-align:left;color:#f5f9ff}
  .hero-pills{
    width:100%;display:flex;flex-wrap:nowrap;justify-content:flex-start;gap:8px;
    overflow-x:auto;padding-bottom:4px;scroll-snap-type:x mandatory;
    -webkit-overflow-scrolling:touch;
  }
  .hero-pills::-webkit-scrollbar{display:none}
  .hero-pill{flex:0 0 auto;scroll-snap-align:start;font-size:11px;padding:8px 11px;background:rgba(9,23,42,.62)}

  .wrap{padding:0 12px 0;max-width:100%;}
  .ops-strip{
    display:flex;gap:10px;overflow-x:auto;margin-top:-58px;margin-bottom:14px;padding:0 1px 6px;
    scroll-snap-type:x mandatory;-webkit-overflow-scrolling:touch;
  }
  .ops-strip::-webkit-scrollbar{display:none}
  .ops-mini{min-width:230px;scroll-snap-align:start;border-radius:17px;padding:13px 14px;box-shadow:0 12px 28px rgba(22,38,63,.13)}
  .ops-icon{width:38px;height:38px;border-radius:14px;font-size:18px}
  .ops-mini strong{font-size:13px}
  .ops-mini span{font-size:11.5px}

  .card{border-radius:18px;padding:16px 14px;margin-bottom:14px;box-shadow:0 1px 2px rgba(22,38,63,.04),0 8px 22px rgba(22,38,63,.08)}
  .card-head{gap:10px;margin-bottom:14px;align-items:flex-start}
  .step{width:31px;height:31px;border-radius:10px;font-size:13px;margin-top:1px}
  .card-head h2{font-size:17px}
  .card-head .sub{font-size:12px;line-height:1.35}

  .grid2{grid-template-columns:1fr;gap:12px}
  .field.full{grid-column:auto}
  label{font-size:12px}
  input[type=text],input[type=date],input[type=number],textarea,select{
    min-height:52px;border-radius:13px;padding:13px 14px;background:#f8fbff;
  }
  textarea{min-height:96px}
  .dir-pick input{padding-left:43px!important;white-space:nowrap;text-overflow:ellipsis;overflow:hidden}
  .dir-pick svg{left:14px;width:19px;height:19px}
  .ac-list{max-height:48vh;border-radius:13px;z-index:80}
  .ac-item{padding:13px 14px;font-size:15px}

  .work-search{min-height:54px;border-radius:14px;margin-bottom:12px}
  .work-grid{grid-template-columns:1fr;gap:10px}
  .work-card{min-height:62px;border-radius:14px;padding:14px 42px 14px 14px;background:#fbfdff}
  .work-card .nm{font-size:13.5px;font-weight:800}
  .work-card .check{right:13px;width:22px;height:22px}
  .add-work{justify-content:flex-start;text-align:left;color:var(--accent-ink);background:var(--accent-soft)}

  #panels{gap:13px;margin-top:14px}
  .panel{border-radius:16px}
  .panel-head{padding:13px 14px}
  .panel-head .ttl{font-size:14.5px;line-height:1.2}
  .panel-head .rm{width:34px;height:34px;border-radius:10px;font-size:20px}
  .panel-body{padding:14px}
  .campos{grid-template-columns:1fr;gap:11px;margin-bottom:12px}
  .panel-body .field{margin-bottom:12px}

  .drop{min-height:124px;border-radius:15px;padding:18px 14px;background:linear-gradient(180deg,#f8fbff,#eef6ff)}
  .drop .ic{font-size:30px}
  .drop .big{font-size:15px}
  .drop small{font-size:12px;max-width:250px}
  .thumbs{grid-template-columns:repeat(2,minmax(0,1fr));gap:10px}
  .thumb{border-radius:13px}
  .thumb input{font-size:13px;min-height:44px}

  .actionbar{padding:10px 12px calc(10px + env(safe-area-inset-bottom));background:rgba(255,255,255,.94);box-shadow:0 -10px 30px rgba(22,38,63,.10)}
  .actionbar-inner{display:grid;grid-template-columns:104px 1fr;gap:9px;max-width:100%}
  .count-pill{grid-column:1/-1;text-align:center;font-size:12px;background:#f1f5fa;border:1px solid var(--line);border-radius:999px;padding:6px 10px;color:var(--ink-soft)}
  .btn{min-height:52px;border-radius:14px;padding:13px 12px;font-size:14px}
  .btn-primary{box-shadow:0 8px 18px rgba(31,111,196,.30)}
  .btn-primary svg{width:17px;height:17px}
  #clearBtn{width:100%}
  #pdfBtn{width:100%}
  .toast{bottom:148px;width:calc(100% - 28px);text-align:center;border-radius:14px;font-size:13px;padding:12px 14px}

  .map-modal{padding:0;align-items:stretch;justify-content:stretch;background:rgba(13,22,38,.72)}
  .map-box{height:100dvh;max-height:none;max-width:none;border-radius:0;width:100%}
  .map-head{padding:13px 14px}
  .map-head .map-title{font-size:14px}
  .map-close{width:38px;height:38px;border-radius:12px;font-size:24px}
  .map-search{display:grid;grid-template-columns:1fr 96px;gap:8px;padding:10px 12px}
  .map-search input{min-width:0;min-height:50px;border-radius:13px}
  .map-search button{min-height:50px;border-radius:13px;padding:0 10px;font-size:13px}
  .map-sug{left:12px;right:12px;top:calc(100% - 2px);max-height:45vh;border-radius:14px}
  .sug-item{padding:12px 13px}
  .pick-map{min-height:46vh}
  .map-foot{padding:11px 12px calc(12px + env(safe-area-inset-bottom));gap:9px}
  .map-addr{font-size:12.5px;max-height:84px;overflow:auto}
  .map-foot .btn{width:100%}

  .saved-card{padding:14px;border-radius:16px;align-items:flex-start}
  .saved-actions{display:grid;grid-template-columns:1fr;gap:9px}
  .saved-actions .btn{min-width:0;width:100%}
}

@media(max-width:380px){
  .hero{min-height:365px;padding-left:12px;padding-right:12px}
  .brand-plate img{height:34px;max-width:190px}
  .hero h1{font-size:33px}
  .hero-sub{font-size:13px}
  .hero-pill{font-size:10.5px;padding:7px 10px}
  .card{padding:14px 12px}
  .thumbs{grid-template-columns:1fr}
  .actionbar-inner{grid-template-columns:92px 1fr}
  .btn{font-size:13.5px}
}


/* ---------- Asistente rápido para técnicos ---------- */
.quick-assistant{background:linear-gradient(180deg,#f7fbff,#eef6ff);border:1px solid #d6e7fb;border-radius:16px;padding:14px;margin:0 0 14px;box-shadow:0 8px 22px rgba(31,111,196,.06)}
.quick-head{display:flex;align-items:flex-start;justify-content:space-between;gap:10px;margin-bottom:10px}
.quick-head strong{font-family:'Archivo';font-size:14px;color:var(--ink)}
.quick-head small{display:block;color:var(--ink-soft);font-size:11.5px;font-weight:700;margin-top:2px}.quick-actions{display:flex;gap:8px;align-items:center;flex-wrap:wrap;justify-content:flex-end}.quick-reset{border:none;background:#fff;color:var(--accent-ink);font-weight:900;border-radius:999px;padding:7px 10px;box-shadow:0 1px 3px rgba(22,38,63,.10);cursor:pointer}.quick-reset:hover{filter:brightness(1.04);transform:translateY(-1px)}
.quick-grid{display:grid;grid-template-columns:1fr 1fr;gap:12px}.quick-group{background:#fff;border:1px solid #e1eaf4;border-radius:14px;padding:11px}.quick-group.full{grid-column:1/-1}.quick-title{font-size:11.5px;font-weight:900;text-transform:uppercase;letter-spacing:.08em;color:#5d6d82;margin-bottom:8px}.chip-row{display:flex;flex-wrap:wrap;gap:7px}.qchip{border:1px solid #cfdae8;background:#fff;color:#243852;border-radius:999px;padding:7px 10px;font-size:12px;font-weight:800;cursor:pointer;line-height:1.15;transition:.15s}.qchip:hover{border-color:var(--accent);background:#f1f7ff}.qchip.on{background:var(--accent);color:#fff;border-color:var(--accent);box-shadow:0 7px 16px rgba(31,111,196,.20)}.qchip.bad.on{background:#e03131;border-color:#e03131}.qchip.warn.on{background:#f59f00;border-color:#f59f00;color:#10213a}.qchip.ok.on{background:#2f9e44;border-color:#2f9e44}.template-row{display:flex;gap:8px;overflow:auto;padding-bottom:3px;margin-bottom:10px}.template-btn{white-space:nowrap;border:1px solid #cfe0f3;background:#fff;border-radius:999px;padding:8px 12px;font-size:12px;font-weight:900;color:var(--accent-ink);cursor:pointer}.template-btn:hover,.template-btn.on{background:var(--accent);color:#fff;border-color:var(--accent);box-shadow:0 7px 16px rgba(31,111,196,.20)}.auto-summary{display:none!important}.mini-equipo{display:grid;grid-template-columns:repeat(4,1fr);gap:10px;background:#f7fafd;border:1px solid var(--line);border-radius:14px;padding:12px;margin-top:4px}.mini-equipo .field input,.mini-equipo .field select{padding:10px 11px;font-size:13.5px}.pti-box{margin-top:10px;background:#fff;border:1px solid #e1eaf4;border-radius:14px;padding:11px}.pti-tools{display:flex;gap:7px;flex-wrap:wrap;margin-bottom:9px}.pti-tools button{border:none;border-radius:999px;padding:7px 10px;font-weight:900;font-size:11.5px;cursor:pointer;background:#edf4fb;color:#155293}.pti-grid{display:grid;grid-template-columns:1fr 1fr;gap:6px}.pti-item{display:grid;grid-template-columns:1fr auto;gap:7px;align-items:center;border:1px solid #e4ebf3;border-radius:10px;padding:8px;background:#fbfdff}.pti-item span{font-size:11.5px;font-weight:750;color:#31455f}.pti-state{display:flex;gap:4px}.pti-state button{border:1px solid #cdd8e5;background:#fff;border-radius:8px;padding:5px 6px;font-size:10.5px;font-weight:900;cursor:pointer;color:#52657d}.pti-state button.on.ok{background:#2f9e44;color:#fff;border-color:#2f9e44}.pti-state button.on.warn{background:#f59f00;color:#10213a;border-color:#f59f00}.pti-state button.on.na{background:#7b8794;color:#fff;border-color:#7b8794}
@media(max-width:720px){.quick-grid{grid-template-columns:1fr}.mini-equipo{grid-template-columns:1fr 1fr}.pti-grid{grid-template-columns:1fr}}
@media(max-width:460px){.mini-equipo{grid-template-columns:1fr}.quick-head{flex-direction:column}.quick-reset{align-self:flex-start}}


/* ---------- Inspección preliminar profesional ---------- */
.mini-equipo.preinspeccion{grid-template-columns:repeat(4,1fr)}
.mini-equipo .field.full{grid-column:1/-1}
.mini-equipo .field textarea{padding:10px 11px;font-size:13.5px;min-height:76px}
.preinspeccion-actions{grid-column:1/-1;display:flex;gap:10px;align-items:center;flex-wrap:wrap;margin-top:2px}
.btn-pre{border:none;background:#1f6fc4;color:#fff;border-radius:12px;padding:11px 14px;font-weight:900;cursor:pointer;box-shadow:0 7px 18px rgba(31,111,196,.22)}
.btn-pre:hover{filter:brightness(1.05)}
.pre-status{font-size:12px;color:#5a6b80;font-weight:800}
@media(max-width:720px){.mini-equipo.preinspeccion{grid-template-columns:1fr 1fr}.preinspeccion-actions{flex-direction:column;align-items:stretch}.btn-pre{width:100%}}
@media(max-width:460px){.mini-equipo.preinspeccion{grid-template-columns:1fr}}


.preloaded-card{border:1.5px solid #cfe1f7!important;background:linear-gradient(180deg,#ffffff,#f8fbff)!important;box-shadow:0 18px 42px rgba(22,38,63,.10)}
.preloaded-card .card-head{align-items:flex-start}
.preloaded-card .step{background:#10213a;color:#fff;border-radius:13px}
.service-summary{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:11px 18px;font-size:13.5px;color:#16263f;margin-top:12px}
.service-summary p{padding:8px 10px;background:#fff;border:1px solid #e1e8f0;border-radius:12px;min-height:38px}
.service-summary p.full{grid-column:1/-1}
.service-summary b{color:#0f2340}
@media(max-width:720px){.service-summary{grid-template-columns:1fr}.service-summary p.full{grid-column:auto}}



/* ---------- Repuestos a considerar ---------- */
.repuestos-card{margin-top:18px;border:1.5px solid #d7e5f5;background:linear-gradient(180deg,#ffffff,#f8fbff);border-radius:20px;padding:18px;box-shadow:0 10px 28px rgba(22,38,63,.06)}
.repuestos-head{display:flex;align-items:flex-start;justify-content:space-between;gap:14px;margin-bottom:12px}
.repuestos-head h3{font-family:'Archivo',system-ui,sans-serif;color:#10213a;font-size:19px;margin:0 0 4px;letter-spacing:-.01em}
.repuestos-head p{margin:0;color:#66758a;font-size:13.5px;font-weight:700;line-height:1.35}
.repuestos-card textarea.tall{min-height:110px}
.repuesto-question-card{margin-top:18px;border:1.5px solid #d7e5f5;background:linear-gradient(180deg,#ffffff,#f7fbff);border-radius:20px;padding:18px;box-shadow:0 10px 28px rgba(22,38,63,.06)}
.repuesto-question-head{display:flex;align-items:flex-start;justify-content:space-between;gap:14px;margin-bottom:12px}.repuesto-question-head h3{font-family:'Archivo',system-ui,sans-serif;color:#10213a;font-size:19px;margin:0 0 4px;letter-spacing:-.01em}.repuesto-question-head p{margin:0;color:#66758a;font-size:13.5px;font-weight:700;line-height:1.35}
.repuesto-choice{display:flex;flex-wrap:wrap;gap:10px}.repuesto-choice button{border:1.5px solid #cfddeb;background:#fff;color:#203854;border-radius:999px;padding:10px 14px;font-family:'Archivo',system-ui,sans-serif;font-size:13px;font-weight:900;cursor:pointer;box-shadow:0 5px 14px rgba(22,38,63,.06)}.repuesto-choice button.on{background:#2f9e44;color:#fff;border-color:#2f9e44;box-shadow:0 10px 22px rgba(47,158,68,.20)}.repuesto-choice button[data-repuesto="no"].on{background:#edf4fb;color:#17385d;border-color:#bcd4ef;box-shadow:none}
.repuestos-card.is-hidden{display:none}.repuesto-tools{display:grid;grid-template-columns:1fr;gap:10px;margin:10px 0 12px}.repuesto-helper{color:#66758a;font-size:12.5px;font-weight:750;line-height:1.35}.repuesto-helper.ok{color:#155293}.repuesto-helper.warn{color:#8a5a00}.repuesto-add-row{display:flex;gap:10px;align-items:center;flex-wrap:wrap;margin-top:10px}.repuesto-add-btn{border:none;background:#1f6fc4;color:#fff;border-radius:14px;padding:11px 14px;font-family:'Archivo',system-ui,sans-serif;font-weight:900;cursor:pointer;box-shadow:0 9px 20px rgba(31,111,196,.18)}.repuesto-add-btn:hover{filter:brightness(.97);transform:translateY(-1px)}.repuesto-selected-list{display:grid;gap:9px;margin:12px 0 4px}.repuesto-selected-item{display:grid;grid-template-columns:1fr 84px 34px;gap:8px;align-items:center;border:1px solid #d7e5f5;background:#fff;border-radius:16px;padding:10px 10px;box-shadow:0 5px 14px rgba(22,38,63,.05)}.repuesto-selected-title{font-weight:900;color:#10213a;line-height:1.25}.repuesto-selected-name{width:100%;border:1.5px solid #d7e5f5;border-radius:12px;padding:9px 10px;font:inherit;font-weight:850;color:#10213a;background:#fff}.repuesto-selected-name:focus{outline:none;border-color:#1f6fc4;box-shadow:0 0 0 3px #e7f0fb}.repuesto-selected-sub{font-size:11.5px;color:#66758a;font-weight:800;margin-top:3px}.repuesto-qty{width:100%;border:1.5px solid #d7e5f5;border-radius:12px;padding:9px 8px;font:inherit;font-weight:900;text-align:center}.repuesto-remove{border:none;background:#fff1f1;color:#b91c1c;border-radius:12px;width:34px;height:34px;font-weight:900;cursor:pointer}.repuesto-empty{display:none;border:1px dashed #c7d9ee;background:#f8fbff;border-radius:16px;padding:12px;color:#66758a;font-weight:800}.repuesto-empty.show{display:block}.is-technical-store{display:none!important}.repuesto-error{display:none;color:#c92a2a;font-size:12px;font-weight:850;margin-top:7px}.repuesto-error.show{display:block}@media(max-width:640px){.repuesto-selected-item{grid-template-columns:1fr 74px 34px}}



/* ---------- Firmas de conformidad ---------- */
.firmas-card{margin-top:18px;border:1.5px solid #d7e5f5;background:linear-gradient(180deg,#ffffff,#f8fbff);border-radius:20px;padding:18px;box-shadow:0 10px 28px rgba(22,38,63,.06)}
.firmas-head{display:flex;align-items:flex-start;justify-content:space-between;gap:14px;margin-bottom:12px}.firmas-head h3{font-family:'Archivo',system-ui,sans-serif;color:#10213a;font-size:19px;margin:0 0 4px;letter-spacing:-.01em}.firmas-head p{margin:0;color:#66758a;font-size:13.5px;font-weight:700;line-height:1.35}
.firmas-grid{display:grid;grid-template-columns:1fr 1fr;gap:12px}.firmas-grid .field.full{grid-column:1/-1}.firma-box{background:#fff;border:1px solid #d7e5f5;border-radius:16px;padding:12px;box-shadow:0 5px 14px rgba(22,38,63,.05)}.firma-title{display:flex;align-items:flex-start;justify-content:space-between;gap:10px;margin-bottom:8px}.firma-title b{font-family:'Archivo',system-ui,sans-serif;color:#10213a;font-size:14px}.firma-title span{font-size:11.5px;color:#66758a;font-weight:800;text-align:right}.firma-canvas{width:100%;height:150px;background:#fff;border:1.5px dashed #bcd4ef;border-radius:14px;touch-action:none;display:block}.firma-actions{display:flex;justify-content:flex-end;margin-top:8px}.firma-clear{border:1px solid #d7e5f5;background:#edf4fb;color:#17385d;border-radius:999px;padding:8px 11px;font-family:'Archivo',system-ui,sans-serif;font-weight:900;cursor:pointer}.firma-clear:hover{filter:brightness(.98);transform:translateY(-1px)}@media(max-width:720px){.firmas-grid{grid-template-columns:1fr}.firma-canvas{height:130px}}

/* ---------- Informe manual profesional (estilos heredados no visibles) ---------- */
.manual-report-card{margin-top:18px;border:1.5px solid #cfe1f7;background:linear-gradient(180deg,#ffffff,#f8fbff);border-radius:20px;padding:18px;box-shadow:0 10px 28px rgba(22,38,63,.06)}
.manual-head{display:flex;align-items:flex-start;justify-content:space-between;gap:14px;margin-bottom:14px}
.manual-head h3{font-family:'Archivo',system-ui,sans-serif;color:#10213a;font-size:19px;margin:0 0 4px;letter-spacing:-.01em}
.manual-head p{margin:0;color:#66758a;font-size:13.5px;font-weight:700;line-height:1.35}
.manual-badge{display:inline-flex;align-items:center;gap:7px;background:#e7f0fb;color:#155293;border:1px solid #cfe1f7;border-radius:999px;padding:8px 11px;font-family:'Archivo',system-ui,sans-serif;font-size:12px;font-weight:900;white-space:nowrap}
.manual-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:12px}
.manual-grid .field.full{grid-column:1/-1}
.manual-grid .field.half{grid-column:span 2}
.param-scroll{overflow-x:auto;border:1px solid #d9e4f1;border-radius:16px;background:#fff}
.param-table{width:100%;min-width:720px;border-collapse:collapse;font-size:13px}
.param-table th{background:#18345c;color:#fff;font-family:'Archivo',system-ui,sans-serif;text-transform:uppercase;letter-spacing:.04em;font-size:11px;padding:9px 8px;text-align:center;border-right:1px solid rgba(255,255,255,.18)}
.param-table th:first-child{text-align:left;width:190px}
.param-table td{border-top:1px solid #d9e4f1;border-right:1px solid #edf2f8;padding:7px;background:#fff}
.param-table td:first-child{font-weight:900;color:#10213a;background:#f7fafd}
.param-table input{width:100%;border:1px solid #d9e4f1;border-radius:10px;padding:9px 10px;background:#f9fbfe;font:inherit;color:#10213a;text-align:center}
.param-table input:focus{outline:none;border-color:#1f6fc4;box-shadow:0 0 0 3px #e7f0fb;background:#fff}
.manual-help{font-size:12.5px;color:#6b7c91;font-weight:700;margin-top:7px}
.manual-report-card textarea{min-height:92px}
.manual-report-card textarea.tall{min-height:128px}
@media(max-width:860px){.manual-grid{grid-template-columns:1fr}.manual-grid .field.half{grid-column:1/-1}.manual-head{flex-direction:column}.manual-badge{white-space:normal}}


/* ---------- Autocompletado cotización / cliente ---------- */
.smart-ac{position:relative}
.smart-menu{position:absolute;left:0;right:0;top:calc(100% + 6px);z-index:70;background:#fff;border:1px solid var(--line);border-radius:14px;box-shadow:0 16px 38px rgba(22,38,63,.16);padding:6px;display:none;max-height:260px;overflow:auto}
.smart-menu.show{display:block}
.smart-option{width:100%;border:none;background:#fff;border-radius:11px;padding:11px 12px;text-align:left;cursor:pointer;display:flex;align-items:flex-start;justify-content:space-between;gap:10px;font-family:inherit;color:var(--ink)}
.smart-option:hover,.smart-option.active{background:var(--accent-soft)}
.smart-main{font-family:'Archivo';font-weight:900;color:var(--ink);font-size:14px;line-height:1.15}
.smart-sub{display:block;color:var(--ink-soft);font-size:12.5px;font-weight:750;margin-top:2px;line-height:1.25}
.smart-badge{background:#eef4fb;color:var(--accent);border:1px solid #cfe1f7;border-radius:999px;padding:4px 8px;font-size:11px;font-weight:900;white-space:nowrap}
.field-hint{margin-top:6px;font-size:11.5px;color:var(--ink-faint);font-weight:750;line-height:1.25}
.field-hint.ok{color:#2f9e44}
.field-hint.warn{color:#b77900}

</style>
</head>
<body>

<style>
.client-menu-home-float{position:fixed;top:18px;left:18px;z-index:120;display:inline-flex;align-items:center;gap:8px;border:1px solid rgba(255,255,255,.32);background:rgba(16,33,58,.86);color:#fff;border-radius:999px;padding:10px 14px;font-family:'Archivo',system-ui,sans-serif;font-size:13px;font-weight:900;cursor:pointer;box-shadow:0 14px 32px rgba(10,21,38,.22);backdrop-filter:blur(12px);transition:.15s}
.client-menu-home-float:hover{background:rgba(31,111,196,.94);transform:translateY(-1px)}
.client-leave-modal{position:fixed;inset:0;z-index:250;display:none;align-items:center;justify-content:center;background:rgba(10,21,38,.58);padding:18px;backdrop-filter:blur(7px)}
.client-leave-modal.show{display:flex}.client-leave-box{width:min(520px,100%);background:#fff;border-radius:24px;border:1px solid #dbe6f3;box-shadow:0 24px 70px rgba(10,21,38,.32);overflow:hidden}.client-leave-head{display:flex;align-items:center;gap:12px;padding:18px 20px;background:linear-gradient(135deg,#f8fbff,#eef5ff);border-bottom:1px solid #dbe6f3}.client-leave-ico{width:42px;height:42px;border-radius:14px;display:grid;place-items:center;background:#fff3cd;color:#8a5a00;font-size:22px;border:1px solid #ffe29a}.client-leave-head h3{font-family:'Archivo',system-ui,sans-serif;margin:0;color:#10213a;font-size:19px;letter-spacing:-.02em}.client-leave-body{padding:18px 20px;color:#5d6d83;font-weight:700;line-height:1.45}.client-leave-actions{display:flex;justify-content:flex-end;gap:10px;padding:0 20px 20px;flex-wrap:wrap}.client-leave-btn{border:none;border-radius:14px;padding:12px 15px;font-family:'Archivo',system-ui,sans-serif;font-weight:900;cursor:pointer}.client-leave-keep{background:#eaf1f8;color:#10213a}.client-leave-exit{background:#1f6fc4;color:#fff;box-shadow:0 10px 22px rgba(31,111,196,.22)}.client-leave-keep:hover,.client-leave-exit:hover{filter:brightness(.97);transform:translateY(-1px)}
@media(max-width:640px){.client-menu-home-float{top:10px;left:10px;padding:9px 11px;font-size:12px}.client-leave-actions{flex-direction:column-reverse}.client-leave-btn{width:100%}}
</style>
<button type="button" class="client-menu-home-float" id="btnClientMenuPrincipal" title="Volver al menú principal">← Menú principal</button>
<div class="client-leave-modal" id="clientLeaveMenuModal" aria-hidden="true"><div class="client-leave-box" role="dialog" aria-modal="true" aria-labelledby="clientLeaveTitle"><div class="client-leave-head"><div class="client-leave-ico">⚠️</div><div><h3 id="clientLeaveTitle">¿Volver al menú principal?</h3></div></div><div class="client-leave-body">Hay datos llenados en Trabajo en cliente. Si vuelves al menú principal sin guardar, podrías perder la información ingresada.</div><div class="client-leave-actions"><button type="button" class="client-leave-btn client-leave-keep" id="btnClientStayHere">Mantenerme aquí</button><button type="button" class="client-leave-btn client-leave-exit" id="btnClientExitMenu">Volver al menú principal</button></div></div></div>
<header class="hero">
  <div class="clock"><span class="time" id="clockTime">--:--:--</span><span class="date" id="clockDate"></span></div>
  <div class="hero-inner">
    <div class="hero-copy">
      <div class="brand-plate"><img id="brandLogo" src="zgroup-logo.png" alt="ZGROUP"></div>
      <span class="kicker"><span class="dot"></span> Área técnica en campo</span>
      <h1>Informe Técnico</h1>
      <p class="hero-sub">Registra N° de reporte, cliente, ubicación exacta, trabajos realizados y evidencias fotográficas del servicio técnico.</p>
      <div class="hero-pills">
        <span class="hero-pill">🧰 Diagnóstico</span>
        <span class="hero-pill"><?= $preEsGenset ? '⚡ Generador' : '❄️ Reefer' ?></span>
        <span class="hero-pill">⚡ Sistema eléctrico</span>
        <span class="hero-pill">📍 Ubicación GPS</span>
      </div>
    </div>
  </div>
</header>

<main class="wrap">

  <?php if ($preinspeccion): ?>
  <section class="card preloaded-card <?= $preEsGenset ? 'preloaded-genset' : 'preloaded-reefer' ?>">
    <div class="card-head">
      <div class="step"><?= $preEsGenset ? '⚡' : '✓' ?></div>
      <div>
        <h2><?= $preEsGenset ? 'Servicio de generador iniciado con inspección preliminar' : 'Servicio reefer iniciado con inspección preliminar' ?></h2>
        <div class="sub">
          Los datos iniciales ya fueron registrados. Ahora completa solo el cierre del trabajo y genera el informe final.
        </div>
      </div>
    </div>

    <div class="service-summary">
      <p><b>Técnico:</b> <?= e($preinspeccion['tecnico_nombre'] ?? '') ?></p>
      <p><b>Estado:</b> <?= e(strtoupper($preinspeccion['estado'] ?? 'abierto')) ?></p>
      <p><b>Cliente:</b> <?= e($preinspeccion['cliente'] ?? '') ?></p>
      <p><b>Reporte:</b> <?= e($preinspeccion['cotizacion'] ?? '') ?></p>
      <p><b>Tipo de equipo:</b> <?= $preEsGenset ? 'Generador (genset)' : 'Contenedor / máquina reefer' ?></p>
      <p><b>Trabajo previsto:</b> <?= e($preinspeccion['trabajo'] ?? '') ?></p>
      <p><b><?= $preEsGenset ? 'Generador' : 'Contenedor / equipo' ?>:</b> <?= e($preinspeccion['numero_equipo'] ?? '') ?></p>
      <p><b>Serial:</b> <?= e($preinspeccion['serie_unidad'] ?? '') ?></p>
      <p><b>Marca / controlador:</b> <?= e($preinspeccion['marca_equipo'] ?? '') ?> / <?= e($preinspeccion['controlador'] ?? '') ?></p>
      <?php if (!$preEsGenset): ?>
        <p><b>Modelo:</b> <?= e($preinspeccion['modelo_equipo'] ?? '-') ?></p>
        <p><b>Año de fabricación:</b> <?= e($preinspeccion['anio_fabricacion'] ?? '-') ?></p>
      <?php endif; ?>
      <p><b>Estado inicial:</b> <?= e($preinspeccion['estado_inicial'] ?? '') ?></p>
      <p><b>Fecha preliminar:</b> <?= e($preinspeccion['creado_en'] ?? '') ?></p>

      <?php if ($preEsGenset): ?>
        <p><b>Horómetro inicial:</b> <?= e($preinspeccion['genset_horometro_inicial'] ?? '-') ?> h</p>
        <p><b>Voltaje de batería:</b> <?= e($preinspeccion['genset_voltaje_bateria_inicial'] ?? '-') ?></p>
        <p><b>Nivel de combustible:</b> <?= e($preinspeccion['genset_nivel_combustible_inicial'] ?? '-') ?></p>
        <p><b>Nivel de aceite:</b> <?= e($preinspeccion['genset_nivel_aceite_inicial'] ?? '-') ?></p>
        <p><b>Refrigerante del motor:</b> <?= e($preinspeccion['genset_refrigerante_motor_inicial'] ?? '-') ?></p>
        <p><b>Prueba de arranque:</b> <?= e($preinspeccion['genset_arranque_inicial'] ?? '-') ?></p>
        <p class="full"><b>Voltajes:</b>
          L1-L2: <?= e($preinspeccion['voltaje_l1_l2'] ?? '-') ?> |
          L2-L3: <?= e($preinspeccion['voltaje_l2_l3'] ?? '-') ?> |
          L1-L3: <?= e($preinspeccion['voltaje_l1_l3'] ?? '-') ?>
        </p>
      <?php else: ?>
        <p><b>Tamaño del contenedor:</b> <?= e($preinspeccion['tamano_contenedor'] ?? '-') ?></p>
        <p><b>Refrigerante:</b> <?= e($preinspeccion['refrigerante'] ?? '-') ?></p>
        <p class="full"><b>Temperaturas:</b>
          Amb: <?= e($preinspeccion['temperatura_ambiente'] ?? '-') ?> °C |
          Ret: <?= e($preinspeccion['retorno_aire'] ?? '-') ?> °C |
          Sum: <?= e($preinspeccion['suministro_aire'] ?? '-') ?> °C |
          Set: <?= e($preinspeccion['set_point'] ?? '-') ?> °C
        </p>
        <p class="full"><b>Presiones:</b>
          Alta: <?= e($preinspeccion['presion_alta'] ?? '-') ?> |
          Baja: <?= e($preinspeccion['presion_baja'] ?? '-') ?>
        </p>
      <?php endif; ?>

      <?php if (trim((string)($preinspeccion['alarma_encontrada'] ?? '')) !== ''): ?>
        <p class="full"><b>Alarma encontrada:</b> <?= e($preinspeccion['alarma_encontrada'] ?? '') ?></p>
      <?php endif; ?>
      <p class="full"><b>Observación inicial:</b> <?= e(trim((string)preg_replace('/\s*\[\[ZG_META:[A-Za-z0-9+\/= _-]+\]\]\s*/x', ' ', (string)($preinspeccion['observacion_inicial'] ?? '')))) ?></p>
    </div>
  </section>
  <?php elseif ($preinspeccionError !== ''): ?>
  <section class="card" style="border:2px solid #ffd6d6;background:#fff7f7;">
    <div class="card-head">
      <div class="step">!</div>
      <div>
        <h2>No se pudo cargar el servicio iniciado</h2>
        <div class="sub"><?= e($preinspeccionError) ?></div>
      </div>
    </div>
  </section>
  <?php endif; ?>

  <section class="ops-strip <?= $preinspeccion ? 'no-overlap' : '' ?>" aria-label="Resumen visual del área técnica">
    <div class="ops-mini"><div class="ops-icon"><?= $preEsGenset ? '⚡' : '🧊' ?></div><div><strong><?= $preEsGenset ? 'Generadores' : 'Equipos reefer' ?></strong><span><?= $preEsGenset ? 'Mantenimiento preventivo y correctivo' : 'Inspección, instalación y reparación' ?></span></div></div>
    <div class="ops-mini"><div class="ops-icon">📷</div><div><strong>Evidencias claras</strong><span>Fotos ordenadas por trabajo</span></div></div>
    <div class="ops-mini"><div class="ops-icon">📄</div><div><strong>PDF automático</strong><span>Informe listo para supervisión</span></div></div>
  </section>

  <!-- 1. Datos generales (formulario existente; datos desde inspecciones_preliminares + odoo_servicios_catalogo) -->
  <?php $datosGenerales = $datosGenerales ?? []; ?>
  <section class="card datos-generales-card <?= $preinspeccion ? 'datos-collapsed' : '' ?>" id="datosGeneralesCard">
    <button type="button" class="datos-toggle" id="datosGeneralesToggle" aria-expanded="<?= $preinspeccion ? 'false' : 'true' ?>">
      <div class="card-head datos-head">
        <div class="step">1</div>
        <div><h2>Datos generales</h2><div class="sub"><?= $preinspeccion ? 'Datos cargados desde la preliminar. Puedes corregirlos antes de generar el informe final.' : 'Información del servicio' ?></div></div>
      </div>
      <div class="datos-side">
        <span class="datos-pill" id="datosGeneralesPill"><?= $preinspeccion ? 'Ver datos' : 'Ocultar datos' ?></span>
        <span class="datos-arrow">⌄</span>
      </div>
    </button>
    <div class="datos-body" id="datosGeneralesBody">
    <div class="grid2">
      <div class="field"><label for="orden">N° de reporte</label>
        <div class="smart-ac">
          <input type="text" id="orden" value="<?= e($datosGenerales['orden'] ?? '') ?>" placeholder="Escribe el inicio. Ej. 12" inputmode="numeric" pattern="[0-9]*" autocomplete="off">
          <div class="smart-menu" id="ordenSuggest"></div>
        </div>
        <div class="field-hint" id="ordenHint">Escribe los primeros números y elige el reporte correcto.</div>
        <div class="field-error" id="ordenError"></div>
      </div>
      <div class="field"><label for="odooTicketRefDisplay">Ticket Odoo</label>
        <input type="text" id="odooTicketRefDisplay" value="<?= e(zgroup_campo($datosGenerales, 'odooTicketRefDisplay')) ?>" placeholder="Automático" readonly>
        <input type="hidden" id="odooTicketRef" value="<?= e(zgroup_campo($datosGenerales, 'odooTicketRef')) ?>">
        <div class="field-error" id="odooTicketRefError"></div>
      </div>
      <input type="hidden" id="odooCotizacionDisplay" value="<?= e(zgroup_campo($datosGenerales, 'odooCotizacionDisplay')) ?>">
      <input type="hidden" id="odooCotizacion" value="<?= e(zgroup_campo($datosGenerales, 'odooCotizacion')) ?>">
      <div class="field"><label for="fecha">Fecha</label><input type="date" id="fecha" value="<?= e($datosGenerales['fecha'] ?? '') ?>"></div>
      <div class="field"><label for="cliente">Cliente</label>
        <input type="text" id="cliente" value="<?= e($datosGenerales['cliente'] ?? '') ?>" placeholder="Se completa al elegir el reporte" readonly>
        <div class="field-error" id="clienteError"></div>
      </div>
      <div class="field"><label for="tecnicoSearch">Técnico</label>
        <div class="autocomplete zg-tech-autocomplete">
          <input type="text" id="tecnicoSearch" class="ac-input" value="<?= e($datosGenerales['tecnico_nombre'] ?? '') ?>" placeholder="Selecciona técnico registrado" autocomplete="off" spellcheck="false">
          <div class="ac-list zg-tech-list" id="tecnicoSuggest" role="listbox"></div>
          <select id="tecnicoInput" class="zg-tech-source" tabindex="-1" aria-hidden="true">
            <option value="">Selecciona técnico registrado</option>
            <?php foreach ($tecnicos as $tec): ?>
              <?php $tecId = (string)($tec['id'] ?? $tec[0] ?? ''); ?>
              <option value="<?= e($tecId) ?>"<?= ($tecId !== '' && $tecId === (string)($datosGenerales['tecnico_id'] ?? '')) ? ' selected' : '' ?>><?= e($tec['nombre'] ?? $tec[1] ?? '') ?></option>
            <?php endforeach; ?>
          </select>
          <input type="hidden" id="tecnicoId" value="<?= e(zgroup_campo($datosGenerales, 'tecnico_id')) ?>">
          <div class="field-error" id="tecnicoInputError"></div>
        </div>
      </div>
      <div class="field full"><label for="direccion">Dirección / ubicación</label>
        <div class="dir-pick" id="dirPick" title="Elegir en el mapa">
          <svg viewBox="0 0 24 24"><path d="M12 21s-7-6.3-7-11a7 7 0 0 1 14 0c0 4.7-7 11-7 11z"/><circle cx="12" cy="10" r="2.5"/></svg>
          <input type="text" id="direccion" value="<?= e($datosGenerales['direccion'] ?? '') ?>" placeholder="Toca para elegir en el mapa…" readonly>
        </div>
        <input type="hidden" id="direccionCoords" value="<?= e(zgroup_campo($datosGenerales, 'direccion_coords')) ?>">
        <input type="hidden" id="direccionOrigenOdoo" value="">
        <div class="field-error" id="direccionError"></div>
      </div>

      <div class="field full"><label>Inspección preliminar del equipo <span class="opt">(antes de realizar el trabajo técnico)</span></label>

        <div class="zg-service-config" id="zgServiceConfig">
          <div class="zg-service-config-head">
            <div>
              <span class="zg-config-kicker">Configuración del servicio</span>
              <h3>Tipo de equipo y configuración del servicio</h3>
              <p>Selecciona primero si se atenderá una máquina reefer o un generador.</p>
            </div>
          </div>
          <div class="zg-service-config-grid zg-type-first-grid">
            <div class="field zg-type-first-field">
              <label for="zgTipoEquipo">¿Qué equipo se atenderá?</label>
              <select id="zgTipoEquipo">
                <option value="">Seleccionar</option>
                <option value="Reefer"<?= zgroup_selected('Reefer', zgroup_campo($datosGenerales, 'zgTipoEquipo')) ?>>Contenedor / máquina reefer</option>
                <option value="Genset"<?= zgroup_selected('Genset', zgroup_campo($datosGenerales, 'zgTipoEquipo')) ?>>Generador (genset)</option>
              </select>
              <div class="field-hint">Selecciona primero el tipo de equipo. Luego aparecerán únicamente los datos que correspondan.</div>
              <div class="field-error" id="zgTipoEquipoError"></div>
            </div>
          </div>

          <div class="zg-equipment-config<?= ($preinspeccion && zgroup_campo($datosGenerales, 'zgTipoEquipo') !== '') ? '' : ' is-hidden' ?>" id="zgCommonServiceConfig">
            <div class="zg-service-config-grid zg-common-config-grid">
              <div class="field">
                <label for="zgModalidadComercial">Modalidad comercial</label>
                <select id="zgModalidadComercial">
                  <option value="">Seleccionar</option>
                  <option value="Alquiler"<?= zgroup_selected('Alquiler', zgroup_campo($datosGenerales, 'zgModalidadComercial')) ?>>Alquiler</option>
                  <option value="Venta"<?= zgroup_selected('Venta', zgroup_campo($datosGenerales, 'zgModalidadComercial')) ?>>Venta</option>
                </select>
                <div class="field-error" id="zgModalidadComercialError"></div>
              </div>
            </div>
          </div>

          <div class="zg-equipment-config<?= ($preinspeccion && !$preEsGenset && zgroup_campo($datosGenerales, 'zgTipoEquipo') !== '') ? '' : ' is-hidden' ?>" id="zgReeferConfig">
            <div class="zg-equipment-config-head">
              <b>❄️ Configuración de la máquina reefer</b>
              <span>Estos campos se muestran únicamente para contenedores o máquinas reefer.</span>
            </div>
            <div class="zg-service-config-grid zg-reefer-config-grid">
              <div class="field">
                <label for="zgTipoInstalacion">Tipo de instalación</label>
                <select id="zgTipoInstalacion">
                  <option value="">Seleccionar</option>
                  <option value="Unidad individual"<?= zgroup_selected('Unidad individual', zgroup_campo($datosGenerales, 'zgTipoInstalacion')) ?>>Unidad individual</option>
                  <option value="Túnel"<?= zgroup_selected('Túnel', zgroup_campo($datosGenerales, 'zgTipoInstalacion')) ?>>Túnel</option>
                  <option value="Atmósfera controlada"<?= zgroup_selected('Atmósfera controlada', zgroup_campo($datosGenerales, 'zgTipoInstalacion')) ?>>Atmósfera controlada</option>
                  <option value="Madurador"<?= zgroup_selected('Madurador', zgroup_campo($datosGenerales, 'zgTipoInstalacion')) ?>>Madurador</option>
                </select>
                <div class="field-error" id="zgTipoInstalacionError"></div>
              </div>
              <div class="field" id="zgTamanoContenedorWrap">
                <label for="zgTamanoContenedor">Tamaño del contenedor</label>
                <select id="zgTamanoContenedor">
                  <option value="">Seleccionar</option>
                  <option value="10 pies"<?= zgroup_selected('10 pies', zgroup_campo($datosGenerales, 'zgTamanoContenedor')) ?>>10 pies</option>
                  <option value="20 pies"<?= zgroup_selected('20 pies', zgroup_campo($datosGenerales, 'zgTamanoContenedor')) ?>>20 pies</option>
                  <option value="40 pies"<?= zgroup_selected('40 pies', zgroup_campo($datosGenerales, 'zgTamanoContenedor')) ?>>40 pies</option>
                  <option value="45 pies"<?= zgroup_selected('45 pies', zgroup_campo($datosGenerales, 'zgTamanoContenedor')) ?>>45 pies</option>
                </select>
                <div class="field-error" id="zgTamanoContenedorError"></div>
              </div>
            </div>
          </div>

          <div class="zg-tunnel-config is-hidden" id="zgTunnelConfig">
            <div class="zg-tunnel-head">
              <div>
                <span class="zg-config-kicker">Túnel de cinco máquinas</span>
                <h4>Registra la marca, el controlador y el número de serie de cada máquina</h4>
              </div>
              <div class="field zg-target-machine">
                <label for="zgMaquinaPreliminarObjetivo">Máquina de referencia en la preliminar</label>
                <select id="zgMaquinaPreliminarObjetivo">
                  <option value="">Seleccionar</option>
                  <option value="M1">Máquina 1</option>
                  <option value="M2">Máquina 2</option>
                  <option value="M3">Máquina 3</option>
                  <option value="M4">Máquina 4</option>
                  <option value="M5">Máquina 5</option>
                </select>
                <div class="field-error" id="zgMaquinaPreliminarObjetivoError"></div>
              </div>
            </div>
            <div class="zg-machines-grid" id="zgMachinesGrid"></div>
            <div class="zg-tunnel-note">La máquina elegida como referencia completará automáticamente los campos de marca, controlador y serie de la inspección preliminar. En cada trabajo realizado podrás indicar cuál máquina fue atendida.</div>
          </div>
        </div>

        <div class="mini-equipo preinspeccion<?= ($preinspeccion && zgroup_campo($datosGenerales, 'zgTipoEquipo') !== '') ? '' : ' is-hidden' ?>" id="zgEquipmentDetails">
          <div class="field"><label for="equipoNo">Contenedor / equipo</label>
            <div class="smart-ac">
              <input type="text" id="equipoNo" value="<?= e(zgroup_campo($datosGenerales, 'equipoNo')) ?>" placeholder="Escribe o selecciona contenedor. Ej. ZGRU01220-7" autocomplete="off">
              <div class="smart-menu" id="contenedorSuggest"></div>
            </div>
            <div class="field-hint" id="contenedorHint"></div>
            <div class="field-error" id="equipoNoError"></div>
          </div>
          <div class="field"><label for="serialUnidad">Serial unidad</label>
            <div class="smart-ac">
              <input type="text" id="serialUnidad" value="<?= e(zgroup_campo($datosGenerales, 'serialUnidad')) ?>" placeholder="Escribe o selecciona serial. Ej. E0G4005148" autocomplete="off">
              <div class="smart-menu" id="maquinaSuggest"></div>
            </div>
            <div class="field-hint" id="maquinaHint"></div>
            <div class="field-error" id="serialUnidadError"></div>
          </div>
          <div class="field"><label for="marcaEquipo">Marca del equipo</label><select id="marcaEquipo"><option value="">—</option><option<?= zgroup_selected('THERMO KING', zgroup_campo($datosGenerales, 'marcaEquipo')) ?>>THERMO KING</option><option<?= zgroup_selected('CARRIER', zgroup_campo($datosGenerales, 'marcaEquipo')) ?>>CARRIER</option><option<?= zgroup_selected('STAR COOL', zgroup_campo($datosGenerales, 'marcaEquipo')) ?>>STAR COOL</option><option<?= zgroup_selected('DAIKIN', zgroup_campo($datosGenerales, 'marcaEquipo')) ?>>DAIKIN</option><option<?= zgroup_selected('OTRO', zgroup_campo($datosGenerales, 'marcaEquipo')) ?>>OTRO</option></select></div>
          <div class="field zg-reefer-pre-field"><label for="modeloEquipo">Modelo</label><input type="text" id="modeloEquipo" value="<?= e(zgroup_campo($datosGenerales, 'modeloEquipo')) ?>" list="modeloEquipoOpciones" placeholder="Selecciona o escribe el modelo" autocomplete="off"><datalist id="modeloEquipoOpciones"></datalist><div class="field-error" id="modeloEquipoError"></div></div>
          <div class="field"><label for="controladorEquipo">Controlador</label><input type="text" id="controladorEquipo" value="<?= e(zgroup_campo($datosGenerales, 'controladorEquipo')) ?>" list="controladorOpciones" placeholder="Selecciona o escribe controlador" autocomplete="off"><datalist id="controladorOpciones"></datalist><div class="field-hint" id="controladorHint">Las opciones cambian según la marca seleccionada.</div><div class="field-error" id="controladorEquipoError"></div></div>
          <div class="field zg-reefer-pre-field"><label for="anioFabricacion">Año de fabricación</label><input type="number" id="anioFabricacion" value="<?= e(zgroup_campo($datosGenerales, 'anioFabricacion')) ?>" min="1980" max="2100" step="1" inputmode="numeric" placeholder="Ej. 2022"><div class="field-error" id="anioFabricacionError"></div></div>
          <div class="field zg-reefer-pre-field"><label for="refrigerante">Refrigerante</label><select id="refrigerante"><option value="">—</option><option<?= zgroup_selected('R404A', zgroup_campo($datosGenerales, 'refrigerante')) ?>>R404A</option><option<?= zgroup_selected('R134a', zgroup_campo($datosGenerales, 'refrigerante')) ?>>R134a</option><option<?= zgroup_selected('R513A', zgroup_campo($datosGenerales, 'refrigerante')) ?>>R513A</option><option<?= zgroup_selected('R452A', zgroup_campo($datosGenerales, 'refrigerante')) ?>>R452A</option><option<?= zgroup_selected('No aplica', zgroup_campo($datosGenerales, 'refrigerante')) ?>>No aplica</option><option<?= zgroup_selected('Otro', zgroup_campo($datosGenerales, 'refrigerante')) ?>>Otro</option></select></div>
          <div class="field zg-reefer-pre-field"><label for="setPoint">Set point °C</label><input type="text" id="setPoint" value="<?= e(zgroup_campo($datosGenerales, 'setPoint')) ?>" placeholder="Ej. -18" inputmode="decimal" step="0.1"><div class="field-error" id="setPointError"></div></div>
          <div class="field zg-reefer-pre-field"><label for="temperaturaAmbiente">Temp. ambiente °C</label><input type="text" id="temperaturaAmbiente" value="<?= e(zgroup_campo($datosGenerales, 'temperaturaAmbiente')) ?>" placeholder="Ej. 24" inputmode="decimal" step="0.1"><div class="field-error" id="temperaturaAmbienteError"></div></div>
          <div class="field zg-reefer-pre-field"><label for="retornoAire">Retorno de aire °C</label><input type="text" id="retornoAire" value="<?= e(zgroup_campo($datosGenerales, 'retornoAire')) ?>" placeholder="Ej. 5" inputmode="decimal" step="0.1"><div class="field-error" id="retornoAireError"></div></div>
          <div class="field zg-reefer-pre-field"><label for="suministroAire">Suministro de aire °C</label><input type="text" id="suministroAire" value="<?= e(zgroup_campo($datosGenerales, 'suministroAire')) ?>" placeholder="Ej. -2" inputmode="decimal" step="0.1"><div class="field-error" id="suministroAireError"></div></div>
          <div class="field zg-reefer-pre-field"><label for="presionAlta">Presión alta</label><input type="text" id="presionAlta" value="<?= e(zgroup_campo($datosGenerales, 'presionAlta')) ?>" placeholder="Ej. 250 PSI" inputmode="decimal" autocomplete="off"><div class="field-error" id="presionAltaError"></div></div>
          <div class="field zg-reefer-pre-field"><label for="presionBaja">Presión baja</label><input type="text" id="presionBaja" value="<?= e(zgroup_campo($datosGenerales, 'presionBaja')) ?>" placeholder="Ej. 18 PSI" inputmode="decimal" autocomplete="off"><div class="field-error" id="presionBajaError"></div></div>
          <div class="field"><label for="voltajeL1L2">Voltaje L1-L2</label><input type="text" id="voltajeL1L2" value="<?= e(zgroup_campo($datosGenerales, 'voltajeL1L2')) ?>" placeholder="Ej. 220 V" inputmode="decimal" autocomplete="off"><div class="field-error" id="voltajeL1L2Error"></div></div>
          <div class="field"><label for="voltajeL2L3">Voltaje L2-L3</label><input type="text" id="voltajeL2L3" value="<?= e(zgroup_campo($datosGenerales, 'voltajeL2L3')) ?>" placeholder="Ej. 220 V" inputmode="decimal" autocomplete="off"><div class="field-error" id="voltajeL2L3Error"></div></div>
          <div class="field"><label for="voltajeL1L3">Voltaje L1-L3</label><input type="text" id="voltajeL1L3" value="<?= e(zgroup_campo($datosGenerales, 'voltajeL1L3')) ?>" placeholder="Ej. 220 V" inputmode="decimal" autocomplete="off"><div class="field-error" id="voltajeL1L3Error"></div></div>
          <div class="field full zg-genset-pre-card<?= ($preinspeccion && $preEsGenset) ? '' : ' is-hidden' ?>" id="zgGensetPreCard">
            <div class="zg-genset-card-head">
              <div><b>⚡ Parámetros iniciales del generador</b><span>Registra cómo se encontró el generador antes de intervenirlo.</span></div>
            </div>
            <div class="zg-genset-grid">
              <div class="field"><label for="gensetHorometroInicial">Horómetro inicial (h)</label><input type="number" id="gensetHorometroInicial" value="<?= e(zgroup_campo($datosGenerales, 'gensetHorometroInicial')) ?>" min="0" step="0.1" inputmode="decimal" placeholder="Ej. 1250.5"><div class="field-error" id="gensetHorometroInicialError"></div></div>
              <div class="field"><label for="gensetVoltajeBateriaInicial">Voltaje de batería inicial</label><input type="text" id="gensetVoltajeBateriaInicial" value="<?= e(zgroup_campo($datosGenerales, 'gensetVoltajeBateriaInicial')) ?>" inputmode="decimal" placeholder="Ej. 12.6 V"><div class="field-error" id="gensetVoltajeBateriaInicialError"></div></div>
              <div class="field"><label for="gensetNivelCombustibleInicial">Nivel de combustible</label><select id="gensetNivelCombustibleInicial"><option value="">Seleccionar</option><option<?= zgroup_selected('Lleno', zgroup_campo($datosGenerales, 'gensetNivelCombustibleInicial')) ?>>Lleno</option><option<?= zgroup_selected('3/4', zgroup_campo($datosGenerales, 'gensetNivelCombustibleInicial')) ?>>3/4</option><option<?= zgroup_selected('1/2', zgroup_campo($datosGenerales, 'gensetNivelCombustibleInicial')) ?>>1/2</option><option<?= zgroup_selected('1/4', zgroup_campo($datosGenerales, 'gensetNivelCombustibleInicial')) ?>>1/4</option><option<?= zgroup_selected('Reserva', zgroup_campo($datosGenerales, 'gensetNivelCombustibleInicial')) ?>>Reserva</option><option<?= zgroup_selected('Vacío', zgroup_campo($datosGenerales, 'gensetNivelCombustibleInicial')) ?>>Vacío</option></select><div class="field-error" id="gensetNivelCombustibleInicialError"></div></div>
              <div class="field"><label for="gensetNivelAceiteInicial">Nivel de aceite</label><select id="gensetNivelAceiteInicial"><option value="">Seleccionar</option><option<?= zgroup_selected('Correcto', zgroup_campo($datosGenerales, 'gensetNivelAceiteInicial')) ?>>Correcto</option><option<?= zgroup_selected('Bajo', zgroup_campo($datosGenerales, 'gensetNivelAceiteInicial')) ?>>Bajo</option><option<?= zgroup_selected('Sobre el nivel', zgroup_campo($datosGenerales, 'gensetNivelAceiteInicial')) ?>>Sobre el nivel</option><option<?= zgroup_selected('No se pudo verificar', zgroup_campo($datosGenerales, 'gensetNivelAceiteInicial')) ?>>No se pudo verificar</option></select><div class="field-error" id="gensetNivelAceiteInicialError"></div></div>
              <div class="field"><label for="gensetRefrigeranteMotorInicial">Refrigerante del motor</label><select id="gensetRefrigeranteMotorInicial"><option value="">Seleccionar</option><option<?= zgroup_selected('Nivel correcto', zgroup_campo($datosGenerales, 'gensetRefrigeranteMotorInicial')) ?>>Nivel correcto</option><option<?= zgroup_selected('Nivel bajo', zgroup_campo($datosGenerales, 'gensetRefrigeranteMotorInicial')) ?>>Nivel bajo</option><option<?= zgroup_selected('Con fuga visible', zgroup_campo($datosGenerales, 'gensetRefrigeranteMotorInicial')) ?>>Con fuga visible</option><option<?= zgroup_selected('No se pudo verificar', zgroup_campo($datosGenerales, 'gensetRefrigeranteMotorInicial')) ?>>No se pudo verificar</option></select><div class="field-error" id="gensetRefrigeranteMotorInicialError"></div></div>
              <div class="field"><label for="gensetArranqueInicial">Prueba de arranque inicial</label><select id="gensetArranqueInicial"><option value="">Seleccionar</option><option<?= zgroup_selected('Arranca normalmente', zgroup_campo($datosGenerales, 'gensetArranqueInicial')) ?>>Arranca normalmente</option><option<?= zgroup_selected('Arranque dificultoso', zgroup_campo($datosGenerales, 'gensetArranqueInicial')) ?>>Arranque dificultoso</option><option<?= zgroup_selected('No arranca', zgroup_campo($datosGenerales, 'gensetArranqueInicial')) ?>>No arranca</option><option<?= zgroup_selected('No se realizó por seguridad', zgroup_campo($datosGenerales, 'gensetArranqueInicial')) ?>>No se realizó por seguridad</option></select><div class="field-error" id="gensetArranqueInicialError"></div></div>
            </div>
          </div>

          <div class="field full estado-inicial-pro">
            <label>Estado inicial del equipo</label>
            <input type="hidden" id="estadoInicial" value="<?= e(zgroup_campo($datosGenerales, 'estadoInicial')) ?>">
            <div class="estado-grid">
              <div class="estado-box">
                <span class="estado-label">Funcionamiento</span>
                <select id="estadoEncendido"><option value="">Seleccionar</option><option value="Encendido"<?= zgroup_selected('Encendido', zgroup_campo($datosGenerales, 'estadoEncendido')) ?>>Encendido</option><option value="Apagado"<?= zgroup_selected('Apagado', zgroup_campo($datosGenerales, 'estadoEncendido')) ?>>Apagado</option></select>
              </div>
              <div class="estado-box">
                <span class="estado-label">Suministro eléctrico</span>
                <select id="estadoEnergia"><option value="">Seleccionar</option><option value="Con suministro eléctrico"<?= zgroup_selected('Con suministro eléctrico', zgroup_campo($datosGenerales, 'estadoEnergia')) ?>>Con suministro eléctrico</option><option value="Sin suministro eléctrico"<?= zgroup_selected('Sin suministro eléctrico', zgroup_campo($datosGenerales, 'estadoEnergia')) ?>>Sin suministro eléctrico</option></select>
              </div>
              <div class="estado-box">
                <span class="estado-label">Alarma</span>
                <select id="estadoAlarma"><option value="">Seleccionar</option><option value="Con alarma"<?= zgroup_selected('Con alarma', zgroup_campo($datosGenerales, 'estadoAlarma')) ?>>Con alarma</option><option value="Sin alarma"<?= zgroup_selected('Sin alarma', zgroup_campo($datosGenerales, 'estadoAlarma')) ?>>Sin alarma</option></select>
              </div>
            </div>
            <div class="zg-alarm-found<?= zgroup_campo($datosGenerales, 'alarmaEncontrada') !== '' ? '' : ' is-hidden' ?>" id="zgAlarmaEncontradaWrap"<?= zgroup_campo($datosGenerales, 'alarmaEncontrada') !== '' ? '' : ' hidden' ?>>
              <label for="alarmaEncontrada">Código o número de alarma</label>
              <input type="text" id="alarmaEncontrada" value="<?= e(zgroup_campo($datosGenerales, 'alarmaEncontrada')) ?>" placeholder="Escribe exactamente lo que aparece en la pantalla. Ej. AL15" autocomplete="off">
              <div class="field-error" id="alarmaEncontradaError"></div>
            </div>
            <div class="field-hint">Selecciona las 3 condiciones del equipo antes de guardar la inspección preliminar.</div>
          </div>
          <div class="field full"><label for="observacionInicial">Observación inicial</label><textarea id="observacionInicial" placeholder="Describe cómo se encontró el equipo antes de intervenirlo."><?= e(zgroup_campo($datosGenerales, 'observacionInicial')) ?></textarea><div class="field-error" id="observacionInicialError"></div></div>

          <div class="field full">
            <div class="pre-evidence-card" id="preEvidenceCard">
              <div class="pre-evidence-head">
                <div>
                  <h3>📸 Evidencias antes de la intervención</h3>
                  <p>Sube fotos que respalden el estado inicial descrito por el técnico. Estas evidencias aparecerán en el informe final.</p>
                </div>
                <span class="pre-evidence-count" id="preEvidenceCount">0 foto(s)</span>
              </div>
              <div class="pre-evidence-source-actions">
                <button type="button" class="pre-evidence-drop" id="preEvidenceCameraBtn">
                  <span class="pre-evidence-ic">📷</span>
                  <span><b>Tomar foto</b><small>Abre directamente la cámara posterior</small></span>
                </button>
                <button type="button" class="pre-evidence-drop" id="preEvidenceGalleryBtn">
                  <span class="pre-evidence-ic">🖼️</span>
                  <span><b>Elegir de la galería</b><small>Selecciona una o varias imágenes guardadas</small></span>
                </button>
              </div>
              <input type="file" id="preEvidenceCameraInput" accept="image/*" capture="environment" hidden>
              <input type="file" id="preEvidenceInput" accept="image/*" multiple hidden>
              <div class="pre-evidence-grid" id="preEvidenceGrid">
                <div class="pre-evidence-empty">Aún no hay evidencias preliminares.</div>
              </div>
            </div>
          </div>

          <input type="hidden" id="preinspeccionId" value="<?= e($preinspeccion['id'] ?? '') ?>">
          <input type="hidden" id="tokenContinuacion" value="<?= e($token_continuacion) ?>">
          <div class="preinspeccion-actions">
            <button type="button" class="btn-pre" id="preBtn">Guardar inspección preliminar</button>
            <span class="pre-status" id="preStatus">Pendiente de preguardado</span>
          </div>
        </div>
      </div>
      <input type="hidden" id="obs" value="">
    </div>
    </div>
  </section>

  <!-- 2. Trabajos realizados -->
  <section class="card" id="trabajosServicioCard">
    <div class="card-head">
      <div class="step">2</div>
      <div><h2>Trabajos realizados</h2><div class="sub">Selecciona los que apliquen. Cada uno abre su propia sección.</div></div>
    </div>
    <input type="text" id="workSearch" class="work-search" placeholder="Buscar trabajo…  (ej. instalación, reparación)" autocomplete="off">
    <div class="work-grid" id="workGrid"></div>
    <div id="panels"></div>

    <div class="repuesto-question-card" id="repuestoQuestionCard">
      <div class="repuesto-question-head">
        <div>
          <h3>🧩 ¿Se necesita repuesto?</h3>
          <p>Marca “Sí” únicamente cuando el servicio queda pendiente por material o cuando se debe solicitar un componente.</p>
        </div>
      </div>
      <input type="hidden" id="requiereRepuesto" value="no">
      <div class="repuesto-choice" role="group" aria-label="¿Se necesita repuesto?">
        <button type="button" data-repuesto="no" id="repuestoNoBtn" class="on">No requiere repuesto</button>
        <button type="button" data-repuesto="si" id="repuestoSiBtn">Sí, requiere repuesto</button>
      </div>
    </div>

    <div class="repuestos-card is-hidden" id="repuestosCard">
      <div class="repuestos-head">
        <div>
          <h3>🧩 Repuestos a considerar</h3>
          <p>Escribe el material. Si ya está en la lista, selecciónalo; si es nuevo, se guardará para que el supervisor lo complete en el panel.</p>
        </div>
      </div>
      <div class="repuesto-tools">
        <div class="field full">
          <label for="repuestoSearch">Material o repuesto</label>
          <div class="smart-ac repuesto-smart">
            <input type="text" id="repuestoSearch" placeholder="Ej. contactor, filter dryer, relay, sensor" autocomplete="off">
            <div class="smart-menu" id="repuestoSuggest"></div>
          </div>
          <div class="repuesto-add-row">
            <button type="button" class="repuesto-add-btn" id="repuestoAddManual">Agregar escrito</button>
            <div class="repuesto-helper" id="repuestoHint">Selecciona “usar” o agrega escrito. Luego ajusta la cantidad en la tabla.</div>
          </div>
        </div>
      </div>
      <div class="field full">
        <label>Materiales seleccionados</label>
        <div class="repuesto-empty show" id="repuestosEmpty">Aún no agregaste repuestos.</div>
        <div class="repuesto-selected-list" id="repuestosSelectedList"></div>
        <textarea id="repuestosManual" class="is-technical-store"></textarea>
        <div class="repuesto-error" id="repuestosManualError"></div>
      </div>
    </div>

    <div class="final-control-card" id="finalControlCard">
      <div class="final-control-head">
        <div>
          <h3>✅ Control final del equipo</h3>
          <p>Registra cómo queda el equipo después del trabajo. Esta parte confirma funcionamiento y parámetros finales.</p>
        </div>
      </div>
      <div class="final-grid">
        <div class="field zg-reefer-final-field"><label for="estadoFinalEquipo">Estado final del equipo</label><select id="estadoFinalEquipo"><option value="">Seleccionar estado</option><option>Equipo operativo</option><option>Equipo operativo con observación</option><option>Equipo pendiente por repuesto</option><option>Equipo requiere seguimiento</option><option>Equipo no operativo</option></select></div>
        <div class="field zg-reefer-final-field"><label for="setPointFinal">Set point final °C</label><input type="text" id="setPointFinal" placeholder="Ej. -18" inputmode="decimal" step="0.1"><div class="field-error" id="setPointFinalError"></div></div>
        <div class="field zg-reefer-final-field"><label for="retornoFinal">Retorno de aire final °C</label><input type="text" id="retornoFinal" placeholder="Ej. 5" inputmode="decimal" step="0.1"><div class="field-error" id="retornoFinalError"></div></div>
        <div class="field zg-reefer-final-field"><label for="suministroFinal">Suministro de aire final °C</label><input type="text" id="suministroFinal" placeholder="Ej. -2" inputmode="decimal" step="0.1"><div class="field-error" id="suministroFinalError"></div></div>
        <div class="field zg-reefer-final-field"><label for="voltajeFinalL1L2">Voltaje final L1-L2</label><input type="text" id="voltajeFinalL1L2" placeholder="Ej. 220 V" inputmode="decimal" autocomplete="off"><div class="field-error" id="voltajeFinalL1L2Error"></div></div>
        <div class="field zg-reefer-final-field"><label for="voltajeFinalL2L3">Voltaje final L2-L3</label><input type="text" id="voltajeFinalL2L3" placeholder="Ej. 220 V" inputmode="decimal" autocomplete="off"><div class="field-error" id="voltajeFinalL2L3Error"></div></div>
        <div class="field zg-reefer-final-field"><label for="voltajeFinalL1L3">Voltaje final L1-L3</label><input type="text" id="voltajeFinalL1L3" placeholder="Ej. 220 V" inputmode="decimal" autocomplete="off"><div class="field-error" id="voltajeFinalL1L3Error"></div></div>
        <div class="field zg-reefer-final-field"><label for="tempAmbienteFinal">Temp. ambiente final °C</label><input type="text" id="tempAmbienteFinal" placeholder="Ej. 24" inputmode="decimal" step="0.1"><div class="field-error" id="tempAmbienteFinalError"></div></div>
        <div class="field zg-reefer-final-field"><label for="presionAltaFinal">Presión alta final</label><input type="text" id="presionAltaFinal" placeholder="Ej. 250 PSI" inputmode="decimal" autocomplete="off"><div class="field-error" id="presionAltaFinalError"></div></div>
        <div class="field zg-reefer-final-field"><label for="presionBajaFinal">Presión baja final</label><input type="text" id="presionBajaFinal" placeholder="Ej. 18 PSI" inputmode="decimal" autocomplete="off"><div class="field-error" id="presionBajaFinalError"></div></div>
        <div class="field zg-genset-final-field is-hidden"><label for="gensetEstadoFinal">Estado final del generador</label><select id="gensetEstadoFinal"><option value="">Seleccionar estado</option><option>Operativo</option><option>Operativo con observación</option><option>Pendiente por repuesto</option><option>Requiere seguimiento</option><option>No operativo</option></select><div class="field-error" id="gensetEstadoFinalError"></div></div>
        <div class="field zg-genset-final-field is-hidden"><label for="gensetHorometroFinal">Horómetro final (h)</label><input type="number" id="gensetHorometroFinal" min="0" step="0.1" inputmode="decimal" placeholder="Ej. 1252.0"><div class="field-error" id="gensetHorometroFinalError"></div></div>
        <div class="field zg-genset-final-field is-hidden"><label for="gensetArranqueFinal">Prueba de arranque final</label><select id="gensetArranqueFinal"><option value="">Seleccionar</option><option>Arranque normal</option><option>Arranque con observación</option><option>No arranca</option><option>No se realizó</option></select><div class="field-error" id="gensetArranqueFinalError"></div></div>
        <div class="field zg-genset-final-field is-hidden"><label for="gensetVoltajeBateriaFinal">Voltaje de batería final</label><input type="text" id="gensetVoltajeBateriaFinal" inputmode="decimal" placeholder="Ej. 13.8 V"><div class="field-error" id="gensetVoltajeBateriaFinalError"></div></div>
        <div class="field zg-genset-final-field is-hidden"><label for="gensetVoltajeSalidaL1L2">Voltaje de salida L1-L2</label><input type="text" id="gensetVoltajeSalidaL1L2" inputmode="decimal" placeholder="Ej. 440 V"><div class="field-error" id="gensetVoltajeSalidaL1L2Error"></div></div>
        <div class="field zg-genset-final-field is-hidden"><label for="gensetVoltajeSalidaL2L3">Voltaje de salida L2-L3</label><input type="text" id="gensetVoltajeSalidaL2L3" inputmode="decimal" placeholder="Ej. 440 V"><div class="field-error" id="gensetVoltajeSalidaL2L3Error"></div></div>
        <div class="field zg-genset-final-field is-hidden"><label for="gensetVoltajeSalidaL1L3">Voltaje de salida L1-L3</label><input type="text" id="gensetVoltajeSalidaL1L3" inputmode="decimal" placeholder="Ej. 440 V"><div class="field-error" id="gensetVoltajeSalidaL1L3Error"></div></div>
        <div class="field zg-genset-final-field is-hidden"><label for="gensetTemperaturaMotorFinal">Temperatura del motor final °C</label><input type="number" id="gensetTemperaturaMotorFinal" step="0.1" inputmode="decimal" placeholder="Ej. 82"><div class="field-error" id="gensetTemperaturaMotorFinalError"></div></div>
        <div class="field zg-genset-final-field is-hidden"><label for="gensetNivelCombustibleFinal">Nivel de combustible final</label><select id="gensetNivelCombustibleFinal"><option value="">Seleccionar</option><option>Lleno</option><option>3/4</option><option>1/2</option><option>1/4</option><option>Reserva</option><option>Vacío</option></select><div class="field-error" id="gensetNivelCombustibleFinalError"></div></div>

        <div class="field zg-service-time-field">
          <label for="horaInicioServicio">Hora de inicio del servicio</label>
          <input type="datetime-local" id="horaInicioServicio" readonly aria-readonly="true">
          <div class="field-hint zg-service-time-hint">Se registra automáticamente al guardar la inspección preliminar.</div>
        </div>
        <div class="field zg-service-time-field">
          <label for="horaFinServicio">Hora de finalización del servicio</label>
          <input type="datetime-local" id="horaFinServicio" readonly aria-readonly="true">
          <div class="field-hint zg-service-time-hint">Se registra automáticamente al generar el primer informe final.</div>
        </div>

        <div class="field full zg-final-maint-question">
          <label for="zgRequiereOtroMantenimiento">¿Requiere otro mantenimiento?</label>
          <select id="zgRequiereOtroMantenimiento">
            <option value="">Seleccionar</option>
            <option value="No">No</option>
            <option value="Sí">Sí</option>
          </select>
          <div class="field-error" id="zgRequiereOtroMantenimientoError"></div>
        </div>
        <div class="field full zg-maintenance-type is-hidden" id="zgTipoMantenimientoWrap">
          <label for="zgTipoOtroMantenimiento">Tipo de mantenimiento requerido</label>
          <select id="zgTipoOtroMantenimiento">
            <option value="">Seleccionar</option>
            <option value="Preventivo">Preventivo</option>
            <option value="Correctivo">Correctivo</option>
            <option value="Preventivo y correctivo">Preventivo y correctivo</option>
          </select>
          <div class="field-error" id="zgTipoOtroMantenimientoError"></div>
        </div>
        <div class="field full zg-maintenance-reason is-hidden" id="zgMotivoMantenimientoWrap">
          <label for="zgMotivoOtroMantenimiento">Razón del mantenimiento requerido</label>
          <textarea id="zgMotivoOtroMantenimiento" maxlength="700" placeholder="Ej. Se recomienda un mantenimiento preventivo porque se detectó desgaste en los componentes y conviene atenderlo antes de que genere una falla."></textarea>
          <div class="field-hint">Explícalo con palabras claras para que el cliente entienda qué se encontró, por qué debe atenderse y qué podría ocurrir si se posterga.</div>
          <div class="field-error" id="zgMotivoOtroMantenimientoError"></div>
        </div>
      </div>
      <div class="final-note">Estos datos quedan en el informe final para comparar cómo se encontró el equipo, cómo quedó y si requiere otra intervención.</div>
    </div>

    <div class="firmas-card" id="firmasCard">
      <div class="firmas-head">
        <div>
          <h3>✍️ Conformidad del servicio</h3>
          <p>Registra el nombre del responsable del cliente y las firmas de conformidad al finalizar el trabajo.</p>
        </div>
      </div>
      <div class="firmas-grid">
        <div class="field full"><label for="adminTiendaNombre">Nombre del administrador / responsable del cliente</label><input type="text" id="adminTiendaNombre" placeholder="Ej. Juan Pérez" autocomplete="off"><div class="field-hint">Este nombre aparecerá en el informe junto a su firma.</div></div>
        <div class="field full"><label for="adminTiendaCargo">Cargo del responsable del cliente</label><input type="text" id="adminTiendaCargo" placeholder="Ej. Administrador de tienda / Supervisor / Encargado" autocomplete="off"><div class="field-hint">El cargo aparecerá debajo del nombre en el informe.</div></div>
        <div class="firma-box">
          <div class="firma-title"><b>Firma del técnico</b><span id="firmaTecnicoNombre">Se usará el técnico seleccionado</span></div>
          <canvas id="firmaTecnicoCanvas" class="firma-canvas"></canvas>
          <div class="firma-actions"><button type="button" class="firma-clear" id="limpiarFirmaTecnico">Limpiar firma</button></div>
          <input type="hidden" id="firmaTecnico" value="">
        </div>
        <div class="firma-box">
          <div class="firma-title"><b>Firma del responsable</b><span>Cliente / tienda</span></div>
          <canvas id="firmaAdminCanvas" class="firma-canvas"></canvas>
          <div class="firma-actions"><button type="button" class="firma-clear" id="limpiarFirmaAdmin">Limpiar firma</button></div>
          <input type="hidden" id="firmaAdmin" value="">
        </div>
      </div>
    </div>
  </section>

  <div id="savedBox" class="savedbox"></div>

</main>

<div class="actionbar">
  <div class="actionbar-inner">
    <span class="count-pill" id="counter">0 trabajos · 0 fotos</span>
    <button class="btn btn-ghost" id="clearBtn">Limpiar</button>
    <button class="btn btn-primary" id="pdfBtn">
      <svg viewBox="0 0 24 24"><path d="M12 3v12m0 0l-4-4m4 4l4-4M4 17v2a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-2"/></svg>
      Generar y guardar
    </button>
  </div>
</div>

<div class="overlay" id="overlay">
  <div>
    <div class="spinner"></div>
    <div class="msg">Generando informe...</div>
  </div>
</div>

<div class="toast" id="toast"></div>

<!-- imágenes ocultas que usa el PDF -->
<img id="pdfBg" src="zgroup-bg.jpg" alt="" style="display:none">

<!-- Modal de mapa para elegir ubicación -->
<div class="map-modal" id="mapModal">
  <div class="map-box">
    <div class="map-head">
      <span class="map-title">📍 Elige la ubicación del servicio</span>
      <button class="map-close" id="mapClose" type="button">×</button>
    </div>
    <div class="map-search">
      <input type="text" id="mapSearch" placeholder="Buscar dirección o lugar…" autocomplete="off">
      <button id="mapSearchBtn" type="button">Buscar</button>
      <div class="map-sug" id="mapSug"></div>
    </div>
    <div id="pickMap" class="pick-map"></div>
    <div class="map-foot">
      <div class="map-addr" id="pickAddr">Toca el mapa o arrastra el pin para elegir el punto exacto…</div>
      <button class="btn btn-primary" id="mapConfirm" type="button">Usar esta ubicación</button>
      <a class="btn btn-ghost map-route" id="mapRoute" href="#" target="_blank" rel="noopener" style="display:none">🧭 Cómo llegar en Google Maps</a>
    </div>
  </div>
</div>

<style>
/* ============================================================
   ZGROUP - tabla real de repuestos seleccionados
   ============================================================ */
#repuestosSelectedList.zg-table-ready{display:block!important;margin-top:12px!important}
.zg-repuestos-table-wrap{border:1.5px solid #cfe0f4;border-radius:18px;overflow:hidden;background:#fff;box-shadow:0 14px 30px rgba(16,33,58,.10);margin-top:10px}
.zg-repuestos-table{width:100%;border-collapse:collapse;font-size:13px;background:#fff}
.zg-repuestos-table thead th{background:linear-gradient(180deg,#eef6ff,#e7f0fb);color:#155293;font-family:'Archivo',system-ui,sans-serif;font-size:11.5px;letter-spacing:.08em;text-transform:uppercase;text-align:left;padding:11px 12px;border-bottom:1px solid #d7e5f5;border-right:1px solid #d7e5f5}
.zg-repuestos-table thead th:last-child{border-right:none;text-align:center}
.zg-repuestos-table td{padding:10px 12px;border-bottom:1px solid #edf3fa;border-right:1px solid #edf3fa;vertical-align:middle}
.zg-repuestos-table td:last-child{border-right:none;text-align:center}.zg-repuestos-table tr:last-child td{border-bottom:none}
.zg-rep-code2{font-family:'Archivo',system-ui,sans-serif;font-weight:900;color:#1f6fc4;word-break:break-word}.zg-rep-code2.empty{color:#8a5a00}
.zg-rep-detail2{width:100%;border:1.5px solid #d7e5f5;background:#f8fbff;border-radius:12px;padding:10px 11px;font:inherit;font-weight:850;color:#10213a;outline:none}.zg-rep-detail2:focus{border-color:#1f6fc4;background:#fff;box-shadow:0 0 0 3px #e7f0fb}
.zg-rep-qty2{width:96px;border:1.5px solid #d7e5f5;background:#fff;border-radius:12px;padding:10px 8px;font:inherit;font-family:'Archivo',system-ui,sans-serif;font-weight:900;text-align:center;color:#10213a;outline:none}.zg-rep-qty2:focus{border-color:#1f6fc4;box-shadow:0 0 0 3px #e7f0fb}
.zg-rep-del2{width:36px;height:36px;border:none;border-radius:12px;background:#fff1f1;color:#b91c1c;font-size:18px;font-weight:900;cursor:pointer}.zg-rep-del2:hover{filter:brightness(.96);transform:translateY(-1px)}
.zg-rep-count2{display:inline-flex!important;align-items:center;gap:8px;background:#eaf7ef;color:#176b34;border:1px solid #bfe8cc;border-radius:999px;padding:8px 12px;font-size:12px;font-weight:900;margin-top:10px}.zg-rep-count2:before{content:'✓'}
.zg-rep-tip2{font-size:11.5px;color:#66758a;font-weight:800;margin-top:4px}.zg-rep-tip2 b{color:#10213a}
#repuestosEmpty{margin-top:12px!important}
.repuesto-smart .smart-menu{z-index:99999!important;pointer-events:auto!important}.repuesto-smart .smart-option{pointer-events:auto!important;cursor:pointer!important}.repuesto-smart .smart-option *{pointer-events:none!important}
@media(max-width:720px){.zg-repuestos-table thead{display:none}.zg-repuestos-table,.zg-repuestos-table tbody,.zg-repuestos-table tr,.zg-repuestos-table td{display:block;width:100%}.zg-repuestos-table tr{border-bottom:1px solid #edf3fa}.zg-repuestos-table td{border-right:none}.zg-repuestos-table td::before{display:block;margin-bottom:5px;font-size:10.5px;letter-spacing:.08em;text-transform:uppercase;color:#66758a;font-family:'Archivo';font-weight:900}.zg-repuestos-table td[data-label="Código"]::before{content:'Código'}.zg-repuestos-table td[data-label="Material"]::before{content:'Material / repuesto'}.zg-repuestos-table td[data-label="Cantidad"]::before{content:'Cantidad'}.zg-rep-qty2{width:130px}.zg-rep-del2{width:100%}}
</style>
<style>
/* Ajuste final: tabla de repuestos más clara y cantidad editable sin quedarse pegada al 1 */
.zg-repuestos-table-wrap{border:1.5px solid #c6dcf5!important;border-radius:18px!important;overflow:hidden!important;background:#fff!important;box-shadow:0 14px 34px rgba(16,33,58,.12)!important}
.zg-repuestos-table{font-size:13.2px!important;background:#fff!important}
.zg-repuestos-table thead th{background:linear-gradient(180deg,#18345c,#10213a)!important;color:#fff!important;padding:12px 12px!important;border-right:1px solid rgba(255,255,255,.18)!important;text-align:left!important}
.zg-repuestos-table thead th:nth-child(3),.zg-repuestos-table thead th:nth-child(4){text-align:center!important}
.zg-repuestos-table td{background:#fff!important;padding:11px 12px!important;border-bottom:1px solid #edf3fa!important;border-right:1px solid #edf3fa!important}
.zg-repuestos-table tbody tr:nth-child(even) td{background:#f8fbff!important}
.zg-rep-code2{font-size:12.8px!important;color:#1f6fc4!important}.zg-rep-code2.empty{color:#9b6a00!important}
.zg-rep-detail2{background:#fff!important;border:1.5px solid #d4e2f3!important;border-radius:12px!important;min-height:42px!important}
.zg-rep-detail2:focus,.zg-rep-qty2:focus{border-color:#1f6fc4!important;box-shadow:0 0 0 3px #e7f0fb!important}
.zg-rep-qty2{width:92px!important;background:#fff!important;border:1.5px solid #d4e2f3!important;border-radius:12px!important;font-size:15px!important;color:#10213a!important;text-align:center!important;caret-color:#1f6fc4!important}
.zg-rep-del2{background:#fff1f1!important;color:#c92a2a!important;border:1px solid #ffd6d6!important}
.zg-rep-count2{margin-bottom:9px!important;background:#eaf7ef!important;color:#176b34!important;border:1px solid #bfe8cc!important}
</style>

<style>
/* ============================================================
   ZGROUP - mejora móvil, evidencias preliminares y firmas
   ============================================================ */
.pre-evidence-card{border:1.5px solid #d7e5f5;background:linear-gradient(180deg,#fff,#f8fbff);border-radius:18px;padding:14px;box-shadow:0 8px 22px rgba(22,38,63,.06);margin-top:4px}
.pre-evidence-head{display:flex;align-items:flex-start;justify-content:space-between;gap:12px;margin-bottom:12px}
.pre-evidence-head h3{font-family:'Archivo',system-ui,sans-serif;font-size:17px;line-height:1.1;color:#10213a;margin:0 0 4px}
.pre-evidence-head p{font-size:12.8px;color:#66758a;font-weight:750;line-height:1.35;margin:0}
.pre-evidence-count{display:inline-flex;align-items:center;background:#e8f2ff;color:#155293;border:1px solid #cbe1fa;border-radius:999px;padding:7px 10px;font-family:'Archivo';font-size:12px;font-weight:900;white-space:nowrap}
.pre-evidence-drop{width:100%;border:1.5px dashed #bcd4ef;background:#f7fbff;border-radius:16px;min-height:74px;display:flex;align-items:center;justify-content:center;gap:12px;color:#10213a;cursor:pointer;text-align:left;padding:13px 16px;transition:.16s}
.pre-evidence-drop:hover{background:#eef6ff;border-color:#82b7ef;transform:translateY(-1px)}
.pre-evidence-ic{font-size:26px;width:44px;height:44px;border-radius:14px;background:#e8f2ff;display:grid;place-items:center;flex:none}
.pre-evidence-drop b{display:block;font-family:'Archivo';font-size:14px}.pre-evidence-drop small{display:block;color:#66758a;font-size:12px;font-weight:800;margin-top:2px}
.pre-evidence-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:10px;margin-top:12px}.pre-evidence-empty{grid-column:1/-1;border:1px dashed #c9d9ec;background:#fff;border-radius:14px;padding:12px;color:#66758a;font-weight:800;font-size:12.5px;text-align:center}
.pre-ev-item{border:1px solid #d7e5f5;background:#fff;border-radius:14px;overflow:hidden;box-shadow:0 6px 16px rgba(22,38,63,.07)}.pre-ev-item img{width:100%;height:88px;object-fit:cover;display:block}.pre-ev-body{padding:8px}.pre-ev-body input{width:100%;border:1px solid #d7e5f5;border-radius:10px;padding:8px;font-size:12px;font-weight:700;color:#10213a}.pre-ev-actions{display:flex;justify-content:space-between;align-items:center;margin-top:7px;font-size:11px;color:#66758a;font-weight:800}.pre-ev-del{border:none;background:#fff1f1;color:#b91c1c;border-radius:999px;padding:5px 8px;font-family:'Archivo';font-weight:900;cursor:pointer}
.firma-box{position:relative}.firma-canvas{height:170px!important;background:linear-gradient(180deg,#fff,#fbfdff)!important;border:2px dashed #9fc4ee!important;border-radius:16px!important;touch-action:none!important;user-select:none!important;cursor:crosshair}.firma-box::after{content:'Firme aquí con el dedo o mouse';position:absolute;left:24px;right:24px;top:86px;text-align:center;color:#9aabc0;font-weight:900;font-size:12px;pointer-events:none}.firma-box.firmado::after{display:none}.firma-clear{min-height:38px}
@media(max-width:820px){body{font-size:15px}.wrap{width:100%!important;padding:0 10px 95px!important}.card{border-radius:18px!important;padding:14px!important}.hero{min-height:240px!important}.mini-equipo.preinspeccion,.final-grid{grid-template-columns:1fr!important}.field input,.field select,.field textarea,.smart-ac input{min-height:46px!important;font-size:15px!important}.pre-evidence-grid{grid-template-columns:1fr 1fr}.pre-ev-item img{height:105px}.sticky{padding:10px!important}.sticky .btn{min-height:50px!important;font-size:14px!important}.firmas-grid{grid-template-columns:1fr!important}.firma-canvas{height:185px!important}.repuestos-card,.final-control-card,.firmas-card{padding:14px!important;border-radius:18px!important}}
@media(max-width:480px){.pre-evidence-grid{grid-template-columns:1fr}.pre-evidence-head{flex-direction:column}.pre-evidence-count{width:max-content}.pre-ev-item img{height:160px}.pre-evidence-drop{justify-content:flex-start}.firma-canvas{height:200px!important}}
</style>

<style id="zg-professional-ui-final">
/* Ajuste visual final: plataforma más limpia para uso en celular y entrega profesional */
body{background:linear-gradient(180deg,#eef4fb 0%,#f6f9fd 45%,#eef3f8 100%)!important;}
.wrap,.container,.main-wrap{max-width:1040px!important;}
.panel,.card,.soft,.step-card,.form-card{border:1px solid #d9e7f5!important;border-radius:22px!important;box-shadow:0 18px 45px rgba(16,33,58,.08)!important;background:rgba(255,255,255,.97)!important;}
.step-head,.panel-head,.card-head-line{border-bottom:1px solid #e4edf7!important;background:linear-gradient(180deg,#ffffff,#f7fbff)!important;}
input,select,textarea{border-radius:13px!important;border-color:#d5e2f0!important;background:#f8fbff!important;}
input:focus,select:focus,textarea:focus{border-color:#1f6fc4!important;box-shadow:0 0 0 4px rgba(31,111,196,.12)!important;background:#fff!important;}
button,.btn,#pdfBtn,#preBtn{border-radius:14px!important;font-weight:900!important;}
.work-card{border-radius:16px!important;background:linear-gradient(180deg,#fff,#f7fbff)!important;border-color:#dbe8f6!important;}
.work-card.on{border-color:#1f6fc4!important;background:linear-gradient(180deg,#eaf4ff,#ffffff)!important;box-shadow:0 12px 28px rgba(31,111,196,.14)!important;}
@media(max-width:720px){
  body{font-size:15px!important;}
  .wrap,.container,.main-wrap{width:100%!important;padding:10px!important;}
  .panel,.card,.soft,.step-card,.form-card{border-radius:18px!important;margin-bottom:14px!important;}
  input,select,textarea{min-height:48px!important;font-size:15px!important;}
  textarea{min-height:96px!important;}
  .bottom-bar,.sticky-actions{padding:9px 10px!important;gap:8px!important;}
  #pdfBtn,#preBtn{min-height:48px!important;font-size:15px!important;}
}
</style>


<style id="zg-firma-suave-bloqueo-trabajos-final">
/* ============================================================
   Firma más profesional + bloqueo de trabajos hasta preliminar
   ============================================================ */
.firma-box{
  position:relative!important;
  background:linear-gradient(180deg,#ffffff,#f8fbff)!important;
  border:1.5px solid #cfe0f3!important;
  border-radius:20px!important;
  padding:14px!important;
}
.firma-title{margin-bottom:8px!important;}
.firma-title b{font-size:15px!important;color:#10213a!important;}
.firma-title span{font-size:12px!important;color:#5f7189!important;}
.firma-helper-zg{
  background:#f2f7fd!important;
  border:1px solid #d6e6f8!important;
  color:#405a76!important;
  border-radius:13px!important;
  padding:8px 10px!important;
  margin:6px 0 10px!important;
  font-size:12.5px!important;
  font-weight:800!important;
}
.firma-canvas{
  height:205px!important;
  border:2px solid #18345c!important;
  border-radius:18px!important;
  background-color:#ffffff!important;
  background-image:
    linear-gradient(to bottom, transparent 72%, rgba(24,52,92,.34) 72%, rgba(24,52,92,.34) calc(72% + 1.5px), transparent calc(72% + 1.5px)),
    radial-gradient(circle at 50% 40%, rgba(31,111,196,.045), transparent 38%)!important;
  box-shadow:inset 0 0 0 1px rgba(255,255,255,.9),0 12px 26px rgba(16,33,58,.08)!important;
  cursor:crosshair!important;
  touch-action:none!important;
  user-select:none!important;
}
.firma-box::after{
  content:'Firme despacio sobre la línea'!important;
  position:absolute!important;
  left:24px!important;
  right:24px!important;
  top:58%!important;
  transform:translateY(-50%)!important;
  text-align:center!important;
  color:#9aacbf!important;
  font-size:13px!important;
  font-weight:900!important;
  pointer-events:none!important;
}
.firma-box.firmado::after{display:none!important;}
.firma-clear{
  background:#edf4fb!important;
  border:1px solid #cfe0f3!important;
  color:#17385d!important;
  border-radius:999px!important;
  min-height:38px!important;
}
@media(max-width:720px){
  .firma-canvas{height:235px!important;}
  .firma-helper-zg{font-size:12.8px!important;line-height:1.3!important;}
}

/* Bloqueo visual de la sección 2 */
.zg-work-locked{
  position:relative!important;
  opacity:.74!important;
  filter:grayscale(.08)!important;
}
.zg-work-locked input,
.zg-work-locked select,
.zg-work-locked textarea,
.zg-work-locked button{
  cursor:not-allowed!important;
}
.zg-work-locked .work-grid,
.zg-work-locked #panels,
.zg-work-locked #repuestoQuestionCard,
.zg-work-locked #repuestosCard,
.zg-work-locked #finalControlCard,
.zg-work-locked #firmasCard{
  pointer-events:none!important;
}
.zg-work-locked .work-card,
.zg-work-locked .repuesto-question-card,
.zg-work-locked .repuestos-card,
.zg-work-locked .final-control-card,
.zg-work-locked .firmas-card{
  background:#f4f8fc!important;
  border-color:#d8e5f3!important;
}
</style>

<style id="zg-firma-modal-cargo-css">
/* Firma grande profesional */
.firma-box{border:1.5px solid #cfddec!important;border-radius:18px!important;background:#fff!important;box-shadow:0 10px 24px rgba(16,33,58,.06)!important;position:relative!important}
.firma-canvas{border:1.5px solid #12345a!important;border-radius:16px!important;background:#fff!important;touch-action:none!important;pointer-events:none!important;cursor:default!important}
.firma-box::after{content:''!important;display:none!important}
.firma-actions{gap:8px!important;flex-wrap:wrap!important;justify-content:space-between!important;align-items:center!important}
.firma-open-big{border:none;background:#1f6fc4;color:#fff;border-radius:999px;padding:9px 13px;font-family:'Archivo',system-ui,sans-serif;font-weight:900;cursor:pointer;box-shadow:0 8px 18px rgba(31,111,196,.20)}
.firma-open-big:hover{filter:brightness(.98);transform:translateY(-1px)}
.firma-status{font-size:12px;font-weight:900;color:#56708c;background:#edf5ff;border:1px solid #cfe1f5;border-radius:999px;padding:7px 10px}
.firma-status.ok{color:#087f3d;background:#eafaf0;border-color:#b7ebc6}
.zg-sign-modal{position:fixed;inset:0;z-index:9999;display:none;align-items:center;justify-content:center;background:rgba(8,20,38,.72);padding:14px;backdrop-filter:blur(8px)}
.zg-sign-modal.show{display:flex}
.zg-sign-box{width:min(980px,100%);height:min(82vh,680px);background:#fff;border-radius:24px;box-shadow:0 30px 90px rgba(0,0,0,.38);overflow:hidden;display:flex;flex-direction:column;border:1px solid #d8e4f2}
.zg-sign-head{display:flex;align-items:flex-start;justify-content:space-between;gap:14px;padding:16px 18px;background:linear-gradient(135deg,#10213a,#1f6fc4);color:#fff}
.zg-sign-head h3{margin:0;font-family:'Archivo',system-ui,sans-serif;font-size:22px;letter-spacing:-.02em}.zg-sign-head p{margin:4px 0 0;color:#dcecff;font-size:13px;font-weight:800}.zg-sign-close{border:none;background:rgba(255,255,255,.18);color:#fff;border-radius:12px;width:42px;height:42px;font-size:20px;font-weight:900;cursor:pointer}
.zg-sign-body{flex:1;padding:16px;background:#f4f8fd;display:flex;flex-direction:column;gap:10px;min-height:0}.zg-sign-area{position:relative;flex:1;min-height:300px;background:#fff;border:2px solid #10213a;border-radius:18px;overflow:hidden;box-shadow:inset 0 0 0 1px #eef4fb}.zg-sign-area canvas{width:100%;height:100%;display:block;touch-action:none;background:#fff}.zg-sign-line{position:absolute;left:7%;right:7%;bottom:22%;height:2px;background:#10213a;opacity:.65;pointer-events:none}.zg-sign-help{position:absolute;left:0;right:0;bottom:24%;text-align:center;color:#8296ad;font-weight:900;font-size:14px;pointer-events:none}.zg-sign-footer{display:flex;align-items:center;justify-content:space-between;gap:10px;flex-wrap:wrap}.zg-sign-tip{font-size:12.5px;color:#60738a;font-weight:800}.zg-sign-actions{display:flex;gap:10px;flex-wrap:wrap}.zg-sign-btn{border:none;border-radius:14px;padding:12px 16px;font-family:'Archivo',system-ui,sans-serif;font-weight:900;cursor:pointer}.zg-sign-btn.clear{background:#eaf1f8;color:#17385d}.zg-sign-btn.cancel{background:#f1f5f9;color:#334155}.zg-sign-btn.save{background:#1f6fc4;color:#fff;box-shadow:0 9px 20px rgba(31,111,196,.25)}
@media(max-width:700px){.zg-sign-box{height:88vh;border-radius:20px}.zg-sign-head h3{font-size:18px}.zg-sign-area{min-height:360px}.zg-sign-footer{align-items:stretch}.zg-sign-actions{width:100%;display:grid;grid-template-columns:1fr 1fr}.zg-sign-btn.save{grid-column:1/-1}.firma-actions{justify-content:flex-start!important}.firma-open-big,.firma-clear{min-height:42px}}
</style>
<style id="zg-salidas-supervision-css">
/* Preparación de salida técnica asignada por supervisor */
.salida-supervision-card{border:1.5px solid #cfe0f5!important;background:linear-gradient(180deg,#ffffff,#f7fbff)!important;border-radius:24px!important;box-shadow:0 16px 42px rgba(16,33,58,.09)!important;overflow:hidden!important;margin-top:18px!important}
.salida-supervision-card .salida-head{display:flex;gap:14px;align-items:flex-start;justify-content:space-between;padding:18px 20px;border-bottom:1px solid #dde8f6;background:linear-gradient(135deg,#10213a,#1f6fc4);color:white}
.salida-supervision-card .salida-title{display:flex;gap:12px;align-items:flex-start}.salida-ico{width:42px;height:42px;border-radius:14px;background:rgba(255,255,255,.14);display:grid;place-items:center;font-size:22px;border:1px solid rgba(255,255,255,.24)}
.salida-supervision-card h3{font-family:'Archivo',system-ui,sans-serif;font-size:19px;margin:0 0 4px;color:#fff;letter-spacing:-.02em}.salida-supervision-card p{margin:0;color:#dcecff;font-weight:750;font-size:13px;line-height:1.35}.salida-pill{background:#eaf7ef;color:#176b34;border:1px solid #bfe8cc;padding:7px 10px;border-radius:999px;font-family:'Archivo';font-size:12px;font-weight:900;white-space:nowrap}
.salida-body{padding:18px 20px}.salida-empty{border:1.4px dashed #cfe0f5;background:#f8fbff;color:#66748a;border-radius:18px;padding:16px 18px;font-weight:800;line-height:1.4}.salida-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:12px;margin-bottom:14px}.salida-info{border:1px solid #dbe6f3;background:#fff;border-radius:16px;padding:12px}.salida-info span{display:block;color:#697891;font-size:11px;font-weight:900;text-transform:uppercase;letter-spacing:.05em;margin-bottom:5px}.salida-info b{display:block;color:#10213a;font-size:14px;line-height:1.25}.salida-apoyo{display:flex;flex-wrap:wrap;gap:8px;margin-top:8px}.salida-chip{display:inline-flex;align-items:center;background:#e8f2ff;color:#13579b;border:1px solid #cbe1fa;border-radius:999px;padding:6px 10px;font-weight:900;font-size:12px}.salida-table-wrap{border:1px solid #d6e3f3;border-radius:18px;overflow:hidden;background:#fff;margin-top:12px}.salida-table{width:100%;border-collapse:collapse;font-size:13px}.salida-table th{background:#10213a;color:#fff;text-align:left;padding:10px 12px;font-family:'Archivo';font-size:12px;letter-spacing:.04em;text-transform:uppercase}.salida-table td{border-top:1px solid #edf3fa;padding:10px 12px;color:#10213a;font-weight:750;vertical-align:top}.salida-table tr:nth-child(even) td{background:#f8fbff}.salida-table .code{color:#1f6fc4;font-weight:950}.salida-note{margin-top:12px;background:#fff8e6;border:1px solid #ffe1a6;color:#745300;border-radius:16px;padding:11px 13px;font-weight:850;font-size:12.5px}
@media(max-width:760px){.salida-supervision-card .salida-head{flex-direction:column;padding:16px}.salida-body{padding:15px}.salida-grid{grid-template-columns:1fr}.salida-table{min-width:620px}.salida-table-wrap{overflow-x:auto}.salida-pill{align-self:flex-start}}
</style>

<style id="zg-reporte-presiones-layout-fix">
/* Ajuste final solicitado: reporte, presiones y PDF más ordenado */
#presionAlta,#presionBaja,#presionAltaFinal,#presionBajaFinal{font-weight:400!important;}
@media(max-width:720px){
  #presionAlta,#presionBaja,#presionAltaFinal,#presionBajaFinal{min-height:48px!important;}
}
</style>
<!-- ZGROUP final: catálogo por controlador, unidades, redacción y evidencias -->
<style id="zg-unidades-materiales-css-final">
  .zg-rep-unit2{display:inline-flex;align-items:center;justify-content:center;min-width:62px;border:1.5px solid #cfe0f3;background:#eef6ff;color:#17385d;border-radius:999px;padding:8px 10px;font-family:'Archivo',system-ui,sans-serif;font-size:12px;font-weight:900;white-space:nowrap}
  .zg-rep-unit-help{display:block;margin-top:5px;color:#6b7d92;font-size:11px;font-weight:800;line-height:1.2}
  #repuestoSuggest .smart-sub .unit-pill{display:inline-flex;margin-left:6px;background:#eaf7ef;color:#176b34;border:1px solid #bfe8cc;border-radius:999px;padding:2px 7px;font-size:11px;font-weight:900}
  @media(max-width:720px){.zg-rep-unit2{justify-content:flex-start;min-width:90px}.zg-rep-unit-help{font-size:11.5px}}
</style>
<style id="zg-final-cleanup-redaccion-evidencias">
  .salida-supervision-card,#salidaSupervisionCard{display:none!important;}
  #repuestoAddManual{display:none!important;}
  #repuestoHint{font-weight:800;color:#5c6e84;}
  .zg-redactor-help{display:flex;align-items:center;gap:10px;flex-wrap:wrap;margin-top:8px;background:#f4f9ff;border:1px solid #d6e7fb;border-radius:14px;padding:9px 10px;color:#40576f;font-size:12px;font-weight:800;}
  .zg-redactor-help label{display:inline-flex;align-items:center;gap:6px;margin:0!important;color:#17385d;font-weight:900;font-size:12px;}
  .zg-redactor-help input{width:auto!important;min-height:0!important;}
  .zg-redactor-help button{border:none;background:#1f6fc4;color:#fff;border-radius:999px;padding:8px 12px;font-family:Archivo,system-ui,sans-serif;font-weight:900;font-size:12px;cursor:pointer;box-shadow:0 8px 18px rgba(31,111,196,.18)}
  .zg-redactor-help button:disabled{opacity:.55;cursor:not-allowed;box-shadow:none;}
  .zg-redactor-help small{color:#6c7f96;font-weight:800;line-height:1.25;}
  @media(max-width:640px){.zg-redactor-help{align-items:flex-start}.zg-redactor-help button{width:100%;min-height:42px}}
</style>
<!-- ZGROUP ajuste final solicitado: sin recomendaciones del técnico en la web -->
<style id="zg-hide-tech-recomendaciones-final">
  .quick-group:has(.quick-title){ }
</style>
<style id="zg-service-config-style">
.zg-service-config{grid-column:1/-1;border:1.5px solid #cfe0f3;background:linear-gradient(145deg,#ffffff,#f5f9ff);border-radius:20px;padding:16px;margin:4px 0 16px;box-shadow:0 10px 26px rgba(16,33,58,.07)}
.zg-service-config-head{display:flex;align-items:flex-start;justify-content:space-between;gap:14px;margin-bottom:14px}.zg-service-config-head h3{font-family:Archivo,system-ui,sans-serif;font-size:19px;color:#10213a;margin:4px 0}.zg-service-config-head p{color:#66758a;font-size:12.5px;font-weight:750}.zg-config-kicker{display:inline-flex;background:#e8f2ff;color:#14599d;border:1px solid #cbe1fa;border-radius:999px;padding:5px 9px;font-size:10px;font-family:Archivo,system-ui,sans-serif;font-weight:900;letter-spacing:.08em;text-transform:uppercase}
.zg-service-config-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:11px}.zg-service-config .field{margin:0}.zg-service-config select,.zg-service-config input{min-height:45px;background:#fff;font-weight:800}.zg-service-config .input-error{border-color:#dc2626!important;box-shadow:0 0 0 3px rgba(220,38,38,.10)!important}
.zg-maintenance-type.is-hidden,.zg-tunnel-config.is-hidden{display:none!important}.zg-tunnel-config{margin-top:15px;border-top:1px solid #dbe7f4;padding-top:15px}.zg-tunnel-head{display:flex;align-items:flex-end;justify-content:space-between;gap:14px;margin-bottom:12px}.zg-tunnel-head h4{font-family:Archivo,system-ui,sans-serif;font-size:16px;color:#10213a;margin-top:5px}.zg-target-machine{width:min(330px,100%)}
.zg-machines-grid{display:grid;gap:9px}.zg-machine-row{display:grid;grid-template-columns:100px 1fr 1fr 1fr;gap:9px;align-items:end;background:#fff;border:1px solid #d8e6f5;border-radius:16px;padding:11px}.zg-machine-badge{align-self:stretch;display:flex;flex-direction:column;align-items:center;justify-content:center;border-radius:12px;background:linear-gradient(145deg,#17365f,#1f6fc4);color:#fff;font-family:Archivo,system-ui,sans-serif;font-weight:900}.zg-machine-badge small{font-size:10px;opacity:.75;margin-bottom:2px}.zg-machine-row .field label{font-size:11px}.zg-machine-row select,.zg-machine-row input{min-height:41px;font-size:13px}.zg-tunnel-note{margin-top:10px;background:#eef6ff;border:1px solid #d1e4fa;color:#526a84;border-radius:12px;padding:9px 11px;font-size:11.5px;font-weight:800;line-height:1.35}.zg-main-machine-synced{background:#eef4fa!important;color:#35506f!important}
.zg-work-machine-field{margin-bottom:13px!important;background:linear-gradient(145deg,#eef6ff,#f8fbff);border:1px solid #cfe1f5;border-radius:15px;padding:11px}.zg-work-machine-field label{display:flex!important;align-items:center;gap:7px;color:#17385d!important;font-family:Archivo,system-ui,sans-serif!important;font-weight:900!important}.zg-work-machine-field label:before{content:'⚙️'}.zg-work-machine-field select{background:#fff!important;min-height:46px!important;font-weight:900!important}.zg-work-machine-field .field-hint{margin-top:5px}
@media(max-width:980px){.zg-service-config-grid{grid-template-columns:1fr 1fr}.zg-machine-row{grid-template-columns:90px 1fr 1fr}.zg-machine-row .zg-machine-serial{grid-column:2/-1}}
@media(max-width:640px){.zg-service-config{padding:13px;border-radius:17px}.zg-service-config-grid{grid-template-columns:1fr}.zg-tunnel-head{align-items:stretch;flex-direction:column}.zg-target-machine{width:100%}.zg-machine-row{grid-template-columns:1fr;padding:10px}.zg-machine-badge{min-height:44px;flex-direction:row;gap:6px}.zg-machine-row .zg-machine-serial{grid-column:auto}.zg-machine-row select,.zg-machine-row input{min-height:48px;font-size:15px}}
</style>
<style id="zg-fix-mantenimiento-posicion-meta">
/* Mantenimiento adicional integrado dentro de Control final */
.final-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:12px}
.final-grid .field{margin:0}
.final-grid .field.full{grid-column:1/-1}
#zgTipoMantenimientoWrap.is-hidden,#zgMotivoMantenimientoWrap.is-hidden{display:none!important}
.zg-final-maint-question,#zgTipoMantenimientoWrap,#zgMotivoMantenimientoWrap{
  border:1.5px solid #cfe0f3;border-radius:15px;background:linear-gradient(180deg,#fff,#f7fbff);padding:12px;
}
.zg-final-maint-question label,#zgTipoMantenimientoWrap label,#zgMotivoMantenimientoWrap label{color:#17385d;font-weight:800}
.zg-final-maint-question select,#zgTipoMantenimientoWrap select{background:#fff!important;font-weight:400!important}
#zgMotivoOtroMantenimiento{min-height:118px!important;background:#fff!important;font-weight:400!important;line-height:1.5}
#zgMotivoMantenimientoWrap .field-hint{margin:7px 1px 0;color:#60748c;font-size:11.8px;font-weight:750;line-height:1.4}
#zgMotivoMantenimientoWrap .zg-redactor-help{margin-top:9px}
#zgPostRepuestoMaintenanceCard{display:none!important}
@media(max-width:700px){.final-grid{grid-template-columns:1fr}.final-grid .field.full{grid-column:auto}}
</style>

<style id="zg-panel-editor-style">
.zg-edit-banner{position:sticky;top:0;z-index:140;margin:0 auto 14px;width:min(980px,calc(100% - 24px));display:none;align-items:center;justify-content:space-between;gap:14px;background:linear-gradient(135deg,#10213a,#1f6fc4);color:#fff;border-radius:0 0 18px 18px;padding:13px 17px;box-shadow:0 12px 30px rgba(16,33,58,.25)}
.zg-edit-banner.show{display:flex}.zg-edit-banner b{font-family:'Archivo',system-ui,sans-serif;font-size:15px}.zg-edit-banner span{display:block;color:#dceafb;font-size:12px;font-weight:750;margin-top:2px}.zg-edit-back{background:#fff;color:#12345a;text-decoration:none;border-radius:999px;padding:9px 13px;font-family:'Archivo';font-weight:900;font-size:12px;white-space:nowrap}
.zg-supervisor-upload{margin-top:10px;padding:11px;border:1px solid #cfe0f3;border-radius:14px;background:#f4f9ff}.zg-supervisor-upload b{display:block;color:#12345a;font-size:12px;margin-bottom:4px}.zg-supervisor-upload small{display:block;color:#66758a;font-weight:700;line-height:1.35;margin-bottom:9px}.zg-supervisor-upload-row{display:flex;gap:8px;align-items:center;flex-wrap:wrap}.zg-supervisor-file{max-width:100%;font-size:12px}.zg-supervisor-upload-btn{border:none;border-radius:999px;background:#1f6fc4;color:#fff;padding:9px 12px;font-family:'Archivo';font-weight:900;cursor:pointer}.zg-supervisor-status{font-size:12px;font-weight:850;color:#176b34}
body.zg-editing #clearBtn{display:none!important}body.zg-editing #preBtn{display:none!important}body.zg-editing .menu-home-float{display:none!important}
body.zg-pre-editing .menu-home-float{display:none!important}
body.zg-pre-editing .actionbar{display:none!important}
body.zg-pre-editing #trabajosServicioCard,
body.zg-pre-editing #finalControlCard,
body.zg-pre-editing #firmasCard{display:none!important}
body.zg-pre-editing #datosGeneralesCard{margin-bottom:40px}
@media(max-width:700px){.zg-edit-banner{align-items:flex-start;flex-direction:column}.zg-edit-back{width:100%;text-align:center}}
</style>
<div id="zgEditBanner" class="zg-edit-banner"><div><b id="zgEditBannerTitle">✏️ Edición de informe desde supervisión</b><span id="zgEditBannerText">Corrige los datos y presiona “Actualizar informe y PDF”.</span></div><a class="zg-edit-back" href="panel.php">Volver al panel</a></div>
<style id="zg-edit-materials-fix-style">
body.zg-editing #repuestoQuestionCard .choice-btn.on{box-shadow:0 0 0 3px rgba(31,111,196,.12)!important}
</style>
<!-- ZGROUP FIX V9 2026-06-20: tamaño contenedor + flujo GENSET + luminarias + guardado real -->
<style id="zg-fix-v8-style">
.zg-genset-pre-card{border:1.5px solid #c9ddf4;border-radius:18px;background:linear-gradient(180deg,#f7fbff,#eef6ff);padding:14px!important;margin-top:6px}
.zg-genset-card-head{display:flex;align-items:center;justify-content:space-between;margin-bottom:10px}.zg-genset-card-head b{display:block;font-family:Archivo,system-ui,sans-serif;color:#10213a;font-size:16px}.zg-genset-card-head span{display:block;color:#637790;font-size:12px;font-weight:750;margin-top:3px}
.zg-genset-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:10px}.zg-genset-grid .field{margin:0}
.zg-genset-final-field{background:linear-gradient(180deg,#fff,#f7fbff);border:1px solid #d7e6f5;border-radius:14px;padding:10px}
body.zg-mode-genset #finalControlCard{border-color:#b9d5f3!important;background:linear-gradient(180deg,#ffffff,#f4f9ff)!important}
body.zg-mode-genset #finalControlCard .final-control-head h3{color:#164f88}
body.zg-mode-genset #refrigerante{pointer-events:none}
@media(max-width:720px){.zg-genset-grid{grid-template-columns:1fr}.zg-genset-final-field{padding:8px}.zg-service-config-grid{grid-template-columns:1fr!important}}
</style>
<!-- ZGROUP V12: alarmas libres + catálogos editables separados por equipo -->
<style id="zg-v12-catalog-style">
#alarmaEncontrada{font-weight:400!important}
</style>
<!-- ZGROUP V14: buscador instantáneo de técnicos -->

<!-- ZGROUP V20: repuestos coherentes, códigos visibles, recomendaciones por equipo y ayudas sin elementos sueltos -->
<style id="zg-v20-final-fixes-style">
  body > .zg-redactor-help,
  .bottom-bar ~ .zg-redactor-help,
  .sticky-actions ~ .zg-redactor-help{display:none!important;}
</style>
<!-- ZGROUP V25: captura definitiva de la fecha editada antes de generar el PDF -->
<!-- ZGROUP V26: la fecha del informe final nunca se restaura desde la preliminar al guardar -->
<!-- ZGROUP V27: horario automático del servicio -->
<style id="zg-v27-service-time-style">
#finalControlCard .zg-service-time-field{background:linear-gradient(180deg,#f7fbff,#eef6ff);border:1px solid #cfe1f4;border-radius:14px;padding:10px}
#finalControlCard .zg-service-time-field input{font-weight:800;color:#17385d;background:#eef5fc!important;cursor:default}
#finalControlCard .zg-service-time-field input.zg-time-supervisor-edit{background:#fff!important;border-color:#1f6fc4!important;cursor:text}
#finalControlCard .zg-service-time-hint{font-size:11px;color:#65788f;margin-top:5px;font-weight:750}
body.zg-editing #finalControlCard .zg-service-time-field{box-shadow:0 0 0 2px rgba(31,111,196,.08)}
@media(max-width:720px){#finalControlCard .zg-service-time-field{grid-column:1/-1}}
</style>
<style id="zg-v39-iphone-camera-sign-css">
/* V39: ingreso de temperaturas negativas y cámara directa en móviles */
.zg-signed-input-wrap{display:flex;align-items:stretch;gap:7px;width:100%}
.zg-signed-input-wrap>input{min-width:0;flex:1}
.zg-sign-toggle{width:48px;min-width:48px;border:1.5px solid #b8d3f2;border-radius:13px;background:#eaf4ff;color:#155293;font-family:'Archivo',system-ui,sans-serif;font-size:24px;font-weight:900;cursor:pointer;line-height:1}
.zg-sign-toggle:hover,.zg-sign-toggle:focus{background:#dceeff;border-color:#1f6fc4;outline:none;box-shadow:0 0 0 3px rgba(31,111,196,.13)}
.zg-sign-toggle.is-negative{background:#1f6fc4;color:#fff;border-color:#1f6fc4}
.pre-evidence-source-actions{display:grid;grid-template-columns:1fr 1fr;gap:10px}
.zg-photo-source-box{cursor:default!important;flex-direction:column!important;text-align:center!important}
.zg-photo-source-actions{display:flex;gap:9px;justify-content:center;width:100%;margin-top:8px}
.zg-photo-source-btn{min-height:42px;border:1.5px solid #bfd7f1;border-radius:12px;background:#fff;color:#17385d;padding:10px 15px;font:inherit;font-family:'Archivo',system-ui,sans-serif;font-weight:900;cursor:pointer}
.zg-photo-source-btn.primary{background:#1f6fc4;color:#fff;border-color:#1f6fc4}
.zg-photo-source-btn:active{transform:scale(.98)}
@media(max-width:640px){
  .pre-evidence-source-actions{grid-template-columns:1fr}
  .pre-evidence-source-actions .pre-evidence-drop{min-height:68px}
  .zg-photo-source-actions{display:grid;grid-template-columns:1fr 1fr}
  .zg-photo-source-btn{width:100%;padding:11px 8px}
  .zg-sign-toggle{width:52px;min-width:52px;font-size:26px}
}
</style>
<style id="zg-panel-catalog-silent">#ordenHint,#clienteHint,#contenedorHint,#maquinaHint,#controladorHint{display:none!important}#orden[readonly],#odooTicketRefDisplay[readonly],#odooCotizacionDisplay[readonly]{background:#eef4fa;color:#17385d;font-weight:800}</style>
</body>
</html>

<!-- ZGROUP: ocultar la ayuda de redacción después de generar el informe -->
<style id="zg-hide-redactor-after-save-style">
  body.zg-informe-guardado .zg-redactor-help{display:none!important;}
</style>
<!-- ZGROUP: limpieza definitiva de informe nuevo y control final -->
<style id="zg-pressure-font-final-v2">
#presionAlta,#presionBaja,#presionAltaFinal,#presionBajaFinal{
  font-family:'Manrope',system-ui,-apple-system,'Segoe UI',sans-serif!important;
  font-weight:400!important;
  font-style:normal!important;
  letter-spacing:normal!important;
}
#presionAlta:-webkit-autofill,#presionBaja:-webkit-autofill,
#presionAltaFinal:-webkit-autofill,#presionBajaFinal:-webkit-autofill{
  -webkit-text-fill-color:#10213a!important;
  font-family:'Manrope',system-ui,-apple-system,'Segoe UI',sans-serif!important;
  font-weight:400!important;
}
</style>
<!-- ============================================================
     ZGROUP - CORRECCIÓN DEFINITIVA
     - La pregunta de mantenimiento reemplaza a Observación final.
     - Elimina tarjetas repetidas fuera de Control final.
     - Elimina ayudas de redacción huérfanas.
     ============================================================ -->
<style id="zg-maintenance-final-definitive-style">
  /* La decisión de mantenimiento forma parte del Control final, no una tarjeta separada. */
  #finalControlCard .zg-final-maint-question,
  #finalControlCard #zgTipoMantenimientoWrap,
  #finalControlCard #zgMotivoMantenimientoWrap{
    grid-column:1 / -1!important;
    width:100%!important;
    max-width:none!important;
    margin:0!important;
  }
  #finalControlCard .zg-final-maint-question{
    margin-top:4px!important;
    padding:14px!important;
    border:1.5px solid #cfe0f3!important;
    border-radius:15px!important;
    background:linear-gradient(180deg,#fff,#f7fbff)!important;
  }
  #finalControlCard .zg-final-maint-question label,
  #finalControlCard #zgTipoMantenimientoWrap label,
  #finalControlCard #zgMotivoMantenimientoWrap label{
    display:block!important;
    margin:0 0 7px!important;
    color:#17385d!important;
    font-family:'Manrope',system-ui,-apple-system,'Segoe UI',sans-serif!important;
    font-size:12px!important;
    font-weight:700!important;
  }
  #finalControlCard #zgRequiereOtroMantenimiento,
  #finalControlCard #zgTipoOtroMantenimiento,
  #finalControlCard #zgMotivoOtroMantenimiento{
    width:100%!important;
    max-width:none!important;
    font-family:'Manrope',system-ui,-apple-system,'Segoe UI',sans-serif!important;
    font-weight:400!important;
  }
  #finalControlCard #zgMotivoOtroMantenimiento{min-height:120px!important;line-height:1.5!important;}
  #zgTipoMantenimientoWrap.is-hidden,
  #zgMotivoMantenimientoWrap.is-hidden{display:none!important;}

  /* Nunca mostrar módulos antiguos/repetidos. */
  #zgPostRepuestoMaintenanceCard,
  .zg-work-followup,
  [data-zg-legacy-maintenance="1"]{display:none!important;}

  /* Una ayuda solo se ve dentro del mismo campo de su textarea. */
  .zg-redactor-help.zg-orphan-help,
  body.zg-informe-guardado .zg-redactor-help,
  body.zg-preliminar-guardada .zg-redactor-help{display:none!important;}
  .field > .zg-redactor-help{width:100%!important;position:static!important;inset:auto!important;}

  @media(max-width:700px){
    #finalControlCard .zg-final-maint-question,
    #finalControlCard #zgTipoMantenimientoWrap,
    #finalControlCard #zgMotivoMantenimientoWrap{grid-column:auto!important;}
  }
</style>
<!-- ============================================================
     ZGROUP FIX V7 2026-06-20
     1) Detalle obligatorio de alarma inicial cuando se marca "Con alarma".
     2) Limpiar conserva la preliminar y borra completamente el cierre.
     3) Menú de materiales móvil: desplazar no selecciona; se elige al tocar.
     ============================================================ -->
<style id="zg-fix-v7-style">
  #zgAlarmaEncontradaWrap{
    margin-top:12px;
    padding:12px;
    border:1px solid #d9e7f5;
    border-radius:14px;
    background:#fff;
  }
  #zgAlarmaEncontradaWrap.is-hidden,
  #zgAlarmaEncontradaWrap[hidden]{display:none!important;}
  #zgAlarmaEncontradaWrap label{
    display:block;
    margin-bottom:7px;
    color:#5f7189;
    font-size:11.5px;
    font-weight:900;
    text-transform:uppercase;
    letter-spacing:.04em;
  }
  #alarmaEncontrada{
    width:100%;
    min-height:46px;
    border:1.5px solid #d4e2f3;
    border-radius:12px;
    padding:10px 12px;
    background:#fff;
    color:#10213a;
    font:inherit;
    font-weight:400!important;
    outline:none;
  }
  #alarmaEncontrada:focus{
    border-color:#1f6fc4;
    box-shadow:0 0 0 3px #e7f0fb;
  }

  /* En celular el listado se puede recorrer verticalmente sin elegir al arrastrar. */
  #repuestoSuggest,
  .zg-work-material-menu{
    touch-action:pan-y!important;
    overscroll-behavior:contain;
    -webkit-overflow-scrolling:touch;
  }
  #repuestoSuggest .smart-option,
  .zg-work-material-menu .zg-work-material-option{
    touch-action:pan-y!important;
    -webkit-tap-highlight-color:transparent;
  }
  @media(max-width:720px){
    #repuestoSuggest,
    .zg-work-material-menu{max-height:52vh!important;overflow-y:auto!important;}
  }
</style>
<!-- ZGROUP V10 2026-06-20: selección inicial Reefer/Generador, formularios exclusivos y advertencia segura -->
<style id="zg-equipment-flow-v10-style">
.zg-type-first-grid{grid-template-columns:minmax(280px,520px)!important}
.zg-type-first-field select{font-size:15px!important;min-height:50px!important;border-width:2px!important}
.zg-equipment-config{margin-top:13px;border-top:1px solid #dbe7f4;padding-top:13px}
.zg-equipment-config.is-hidden,#zgEquipmentDetails.is-hidden,#finalControlCard.zg-type-hidden{display:none!important}
.zg-equipment-config-head{display:flex;flex-direction:column;gap:3px;margin-bottom:10px}
.zg-equipment-config-head b{font-family:Archivo,system-ui,sans-serif;color:#10213a;font-size:15px}
.zg-equipment-config-head span{color:#66758a;font-size:12px;font-weight:750}
.zg-common-config-grid{grid-template-columns:minmax(240px,360px)!important}
.zg-reefer-config-grid{grid-template-columns:repeat(2,minmax(220px,1fr))!important}
body:not(.zg-mode-reefer):not(.zg-mode-genset) #zgEquipmentDetails,
body:not(.zg-mode-reefer):not(.zg-mode-genset) #finalControlCard{display:none!important}
body.zg-mode-genset .zg-reefer-pre-field,
body.zg-mode-genset .zg-reefer-final-field,
body.zg-mode-genset #zgReeferConfig,
body.zg-mode-genset #zgTunnelConfig{display:none!important}
body.zg-mode-reefer #zgGensetPreCard,
body.zg-mode-reefer .zg-genset-final-field{display:none!important}
body.zg-mode-genset #zgGensetPreCard{display:block!important}
body.zg-mode-genset .zg-genset-final-field{display:block!important}
body.zg-mode-reefer .zg-reefer-pre-field,
body.zg-mode-reefer .zg-reefer-final-field{display:block!important}
.zg-type-switch-modal{position:fixed;inset:0;z-index:12000;display:none;align-items:center;justify-content:center;padding:18px;background:rgba(9,24,44,.64);backdrop-filter:blur(7px)}
.zg-type-switch-modal.show{display:flex}
.zg-type-switch-box{width:min(530px,100%);background:#fff;border:1px solid #d9e5f2;border-radius:22px;box-shadow:0 30px 90px rgba(0,0,0,.34);overflow:hidden}
.zg-type-switch-head{display:flex;align-items:center;gap:12px;padding:18px 20px;background:linear-gradient(135deg,#eaf4ff,#ffffff);border-bottom:1px solid #c9def5}
.zg-type-switch-icon{width:44px;height:44px;display:grid;place-items:center;border-radius:14px;background:#dcecff;color:#1f6fc4;font-size:22px}
.zg-type-switch-head h3{font-family:Archivo,system-ui,sans-serif;color:#155293;font-size:19px}
.zg-type-switch-body{padding:18px 20px;color:#53677f;font-weight:700;line-height:1.5}
.zg-type-switch-body strong{color:#10213a}
.zg-type-switch-actions{display:flex;justify-content:flex-end;gap:10px;padding:0 20px 20px}
.zg-type-switch-actions button{border:0;border-radius:12px;padding:11px 15px;font-family:Archivo,system-ui,sans-serif;font-weight:900;cursor:pointer}
.zg-type-switch-cancel{background:#eaf0f6;color:#10213a}.zg-type-switch-confirm{background:#1f6fc4;color:#fff;box-shadow:0 10px 24px rgba(31,111,196,.24)}
@media(max-width:640px){.zg-reefer-config-grid{grid-template-columns:1fr!important}.zg-type-switch-actions{flex-direction:column-reverse}.zg-type-switch-actions button{width:100%}}
</style>
<!-- ZGROUP V11 2026-06-20: catálogos separados, SG-3000/SG-5000 y sugerencias reales de generador -->
<style id="zg-v11-blue-and-genset-style">
.zg-type-switch-head{background:linear-gradient(135deg,#e8f3ff,#fff)!important;border-bottom-color:#c9def5!important}
.zg-type-switch-icon{background:#dcecff!important;color:#1f6fc4!important}
.zg-type-switch-head h3{color:#155293!important}
.zg-type-switch-confirm{background:#1f6fc4!important;color:#fff!important;box-shadow:0 10px 24px rgba(31,111,196,.24)!important}
body.zg-mode-genset .quick-assistant{background:linear-gradient(180deg,#f5faff,#eaf4ff)!important;border-color:#bfd9f4!important}
body.zg-mode-genset .template-btn.on,body.zg-mode-genset .qchip.on{background:#1f6fc4!important;border-color:#1f6fc4!important;color:#fff!important}
</style>
<!-- ZGROUP V19: catálogo de generadores y formularios técnicos simplificados -->
<!-- ZGROUP V38: opciones nuevas persistentes por tipo de equipo -->
<style id="zg-v35-smart-entry-css">
.zg-v35-assistant{margin:14px 0 16px;border:1.5px solid #d5e5f6;border-radius:18px;background:linear-gradient(180deg,#fff,#f8fbff);padding:14px;box-shadow:0 8px 22px rgba(16,33,58,.06);overflow:visible!important}
.zg-v35-grid,.zg-v35-group,.zg-v35-search,.zg-v35-search-wrap{overflow:visible!important}
.zg-v35-head{display:flex;align-items:flex-start;justify-content:space-between;gap:12px;margin-bottom:12px}
.zg-v35-head strong{display:block;font-family:Archivo,system-ui,sans-serif;color:#10213a;font-size:16px}
.zg-v35-head small{display:block;margin-top:3px;color:#66758a;font-size:12px;font-weight:750;line-height:1.35}
.zg-v35-clear{border:1px solid #cfe0f3;background:#eef5fc;color:#17385d;border-radius:999px;padding:8px 11px;font:inherit;font-size:11.5px;font-weight:900;cursor:pointer;white-space:nowrap}
.zg-v35-clear:hover{background:#e1effd}
.zg-v35-grid{display:grid;grid-template-columns:1fr 1fr;gap:12px}
.zg-v35-group{position:relative;border:1px solid #d8e6f5;background:#fff;border-radius:15px;padding:11px;min-width:0}
.zg-v35-group .quick-title{font-family:Archivo,system-ui,sans-serif;font-size:12px;font-weight:900;color:#17385d;margin-bottom:7px}
.zg-v35-search{display:flex;gap:7px;align-items:stretch}
.zg-v35-search-wrap{position:relative;flex:1;min-width:0}
.zg-v35-input{width:100%;min-height:44px;border:1.5px solid #cfe0f3;border-radius:12px;padding:9px 11px;font:inherit;color:#10213a;background:#fff;outline:none}
.zg-v35-input:focus{border-color:#1f6fc4;box-shadow:0 0 0 3px #e7f0fb}
.zg-v35-add{border:0;background:#1f6fc4;color:#fff;border-radius:12px;padding:0 13px;font-family:Archivo,system-ui,sans-serif;font-weight:900;cursor:pointer;box-shadow:0 8px 18px rgba(31,111,196,.18)}
.zg-v35-add:hover{filter:brightness(1.04)}
.zg-v53-ai{border:1px solid #9fc8f3;background:#e8f3ff;color:#155293;border-radius:12px;padding:0 12px;font-family:Archivo,system-ui,sans-serif;font-weight:900;cursor:pointer;white-space:nowrap}
.zg-v53-ai:hover{background:#dcecff}
.zg-v53-ai:disabled{opacity:.6;cursor:wait}
.zg-v53-ai-note{margin-top:6px;color:#667991;font-size:10.5px;font-weight:750;line-height:1.35}

.zg-v35-menu{display:none;position:absolute;z-index:100;left:0;right:0;top:calc(100% + 5px);max-height:230px;overflow:auto;background:#fff;border:1px solid #d5e3f2;border-radius:13px;box-shadow:0 18px 38px rgba(16,33,58,.18);padding:5px}
.zg-v35-menu.show{display:block}
.zg-v35-option{width:100%;border:0;background:#fff;border-radius:9px;padding:9px 10px;text-align:left;color:#10213a;font:inherit;font-size:12.5px;font-weight:800;cursor:pointer}
.zg-v35-option:hover,.zg-v35-option.active{background:#eaf3ff;color:#155293}
.zg-v35-empty{padding:10px;color:#66758a;font-size:12px;font-weight:750;line-height:1.35}
.zg-v35-tags{display:flex;flex-wrap:wrap;gap:7px;margin-top:10px;min-height:10px}
.zg-v35-tags .qchip.on{position:relative;border:1px solid #9fc8f3;background:#e8f3ff;color:#155293;border-radius:999px;padding:7px 27px 7px 10px;font:inherit;font-size:11.5px;font-weight:900;cursor:pointer;text-align:left;line-height:1.2}
.zg-v35-tags .qchip.on::after{content:'×';position:absolute;right:9px;top:50%;transform:translateY(-50%);font-size:15px;line-height:1;color:#4d739d}
.zg-v35-group.warn .zg-v35-tags .qchip.on{border-color:#f5cf7c;background:#fff6db;color:#8a5a00}
.zg-v35-group.warn .zg-v35-tags .qchip.on::after{color:#9d7217}
.zg-v35-tip{margin-top:7px;color:#7b8a9d;font-size:10.8px;font-weight:750;line-height:1.35}
@media(max-width:760px){.zg-v35-grid{grid-template-columns:1fr}.zg-v35-head{align-items:stretch;flex-direction:column}.zg-v35-clear{align-self:flex-start}.zg-v35-search{flex-wrap:wrap}.zg-v35-search-wrap{flex-basis:100%}.zg-v35-add,.zg-v53-ai{min-height:42px;flex:1;padding:0 12px}}
</style>
<style id="zg-service-draft-style">
.zg-draft-tools{display:none;align-items:center;gap:8px;min-width:0;flex-wrap:wrap}
.zg-draft-tools.show{display:flex}
.zg-draft-save{border:1px solid #bcd7f2;background:#eef6ff;color:#155a9c;border-radius:999px;padding:8px 12px;font-family:'Archivo',system-ui,sans-serif;font-size:12px;font-weight:900;cursor:pointer;white-space:nowrap}
.zg-draft-save:hover{background:#dfeeff}
.zg-draft-status{font-size:11px;font-weight:800;color:#60758d;white-space:nowrap}
.zg-draft-status.saving{color:#9a6700}.zg-draft-status.saved{color:#16713c}.zg-draft-status.error{color:#b42318}
@media(max-width:700px){.zg-draft-tools{width:100%;justify-content:center}.zg-draft-status{white-space:normal;text-align:center}}
</style>
<!-- ZGROUP V50: memoria técnica persistente + sugerencias históricas + preventivo reefer completo -->
<!-- ZGROUP V48: checklist reefer compacto, instalación reefer y unidades corregidas -->
<style id="zg-v48-reefer-checklist-style">
.zg-v48-checklist{margin:14px 0 16px;border:1px solid #cfe0f3;border-radius:18px;background:#f8fbff;overflow:hidden}
.zg-v48-checklist summary{cursor:pointer;list-style:none;padding:14px 16px;display:flex;align-items:center;justify-content:space-between;gap:12px;background:linear-gradient(180deg,#fff,#f1f7fe);font-family:Archivo,system-ui,sans-serif;font-weight:900;color:#10213a}
.zg-v48-checklist summary::-webkit-details-marker{display:none}
.zg-v48-checklist summary small{font-family:Manrope,system-ui,sans-serif;color:#65768d;font-weight:700}
.zg-v48-checklist-body{padding:12px}
.zg-v48-simple-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:9px}
.zg-v48-simple-card{border:1px solid #dbe6f3;border-radius:14px;background:#fff;overflow:hidden}
.zg-v48-simple-card:nth-child(even){background:#f8fbff}
.zg-v48-simple-head{display:flex;align-items:flex-start;gap:8px;padding:8px 10px 5px;font-size:11px;font-weight:900;color:#213752}
.zg-v48-num{flex:0 0 24px;width:24px;height:24px;display:grid;place-items:center;border-radius:8px;background:#e8f2ff;color:#1f6fc4;font-family:Archivo,system-ui,sans-serif}
.zg-v48-simple-value{display:grid;grid-template-columns:minmax(0,1fr) auto;gap:7px;align-items:center;padding:5px 9px 9px}
.zg-v48-simple-value input,.zg-v48-simple-value select{width:100%;min-width:0;min-height:38px!important;padding:8px 10px!important;font-size:13px!important}
.zg-v48-unit{display:inline-flex;min-width:46px;justify-content:center;border-radius:999px;background:#e8f2ff;color:#155293;padding:7px 8px;font-weight:900;white-space:nowrap;font-size:11px}
.zg-v48-okvolt{display:grid;grid-template-columns:minmax(120px,.7fr) minmax(120px,1fr);gap:7px}
.zg-v48-three-wrap{margin-top:12px;overflow:auto;border:1px solid #dbe6f3;border-radius:14px;background:#fff}
.zg-v48-three-table{width:100%;border-collapse:collapse;min-width:690px;font-size:11.5px}
.zg-v48-three-table th{background:#10213a;color:#fff;padding:8px;border-right:1px solid rgba(255,255,255,.18);text-align:center;font-family:Archivo,system-ui,sans-serif}
.zg-v48-three-table td{padding:7px;border-right:1px solid #dbe6f3;border-bottom:1px solid #dbe6f3;vertical-align:middle;background:#fff}
.zg-v48-three-table tbody tr:nth-child(even) td{background:#f5f9fe}
.zg-v48-three-table .n{width:42px;text-align:center;font-weight:900;color:#1f6fc4}
.zg-v48-three-table .item{min-width:240px;font-weight:800;color:#213752}
.zg-v48-three-table input{width:100%;min-width:80px;min-height:36px!important;padding:7px 8px!important;font-size:12px!important;text-align:center}
.zg-v48-three-table .unit{text-align:center;font-weight:900;color:#155293}
@media(max-width:760px){.zg-v48-checklist summary{align-items:flex-start;flex-direction:column}.zg-v48-simple-grid{grid-template-columns:1fr}.zg-v48-checklist-body{padding:8px}.zg-v48-okvolt{grid-template-columns:1fr}.zg-v48-three-table{font-size:11px}}
</style>
<!-- ZGROUP V45: checklist reefer correlativo + detalle técnico único + IA con antecedentes -->
<!-- ZGROUP V48: estado inicial de generador sin suministro eléctrico -->
<style id="zg-v48-genset-initial-state-style">
body.zg-mode-genset .zg-energy-state-box{display:none!important}
body.zg-mode-genset .estado-grid{grid-template-columns:repeat(2,minmax(0,1fr))!important}
@media(max-width:640px){body.zg-mode-genset .estado-grid{grid-template-columns:1fr!important}}
</style>
<!-- ZGROUP V51: parámetros eléctricos compactos para instalación y mantenimiento preventivo reefer -->
<style id="zg-v51-parametros-reefer-style">
.zg-v51-parametros{margin:14px 0 16px;border:1px solid #cfe0f3;border-radius:16px;background:#fff;overflow:hidden}
.zg-v51-parametros-head{padding:11px 14px;background:linear-gradient(180deg,#f8fbff,#edf5ff);font-family:Archivo,system-ui,sans-serif;font-weight:900;color:#10213a;border-bottom:1px solid #dbe6f3}
.zg-v51-parametros-sub{display:block;margin-top:3px;font-family:Manrope,system-ui,sans-serif;font-size:11px;font-weight:700;color:#667991}
.zg-v51-parametros-wrap{overflow:auto}
.zg-v51-parametros-table{width:100%;border-collapse:collapse;min-width:610px;font-size:11.5px}
.zg-v51-parametros-table th{background:#10213a;color:#fff;padding:8px;border-right:1px solid rgba(255,255,255,.18);font-family:Archivo,system-ui,sans-serif;text-align:center}
.zg-v51-parametros-table td{padding:7px;border-right:1px solid #dbe6f3;border-bottom:1px solid #dbe6f3;background:#fff}
.zg-v51-parametros-table tbody tr:nth-child(even) td{background:#f6f9fd}
.zg-v51-parametros-table .param{font-weight:900;color:#213752;min-width:180px}
.zg-v51-parametros-table input{width:100%;min-width:80px;min-height:36px!important;padding:7px 8px!important;text-align:center;font-size:12px!important}
.zg-v51-parametros-table .unit{text-align:center;font-weight:900;color:#155293;width:65px}
@media(max-width:760px){.zg-v51-parametros{border-radius:13px}.zg-v51-parametros-table{font-size:11px}}
</style>
<!-- ZGROUP V48 FINAL -->


<!-- ZGROUP V53: tabla reefer en dos columnas + Ohm PDF + IA en actividades/hallazgos + memoria por trabajo -->

<style id="zg-odoo-final-ui">
.zg-client-odoo-grid{padding:12px;border:1px solid #dce8f4;border-radius:16px;background:#f7fbff}.zg-client-odoo-grid .field{margin:0}.zg-client-odoo-grid input[readonly]{background:#fff;color:#17385d;font-weight:750}#zgServicioImportadoWrap{margin-top:2px}#zgServicioImportadoWrap label{font-size:12px;color:#60738a}#zgServicioImportado{min-height:44px}
</style>


<!-- ZGROUP V54: corriente de línea + resistencias y regla de repuesto por modalidad -->
<style id="zg-v54-modalidad-repuesto-style">
.zg-work-commercial-rule{margin:0 0 11px;padding:10px 12px;border-radius:13px;font-size:12px;font-weight:850;line-height:1.45}
.zg-work-commercial-rule.alquiler{background:#fff7e8;border:1px solid #f0d39f;color:#6b4816}
.zg-work-commercial-rule.venta{background:#eef8ff;border:1px solid #beddf4;color:#174f79}
#repuestoNoBtn.zg-disabled-rule{opacity:.55;cursor:not-allowed!important;filter:grayscale(.15)}
.zg-v51-parametros-table .param{min-width:270px!important}
@media(max-width:760px){.zg-v51-parametros-table .param{min-width:235px!important}}
</style>
<!-- ZGROUP V55: interfaz limpia, códigos automáticos y seguimiento comercial reefer -->
<style id="zg-v55-final-rules-style">
.zg-v35-tip,.zg-v53-ai-note{display:none!important}
.zg-work-commercial-rule{white-space:normal}
.zg-v55-sale-summary{margin:12px 0;padding:12px 14px;border:1px solid #b9d9f2;border-radius:14px;background:#eef8ff;color:#174f79;font-size:12px;font-weight:850;line-height:1.5}
.zg-v55-rental-summary{margin:12px 0;padding:12px 14px;border:1px solid #efd29d;border-radius:14px;background:#fff7e8;color:#6b4816;font-size:12px;font-weight:850;line-height:1.5}
</style>
<!-- ZGROUP V56: recuperación definitiva de códigos de materiales -->
<style id="zg-v56-codigos-materiales-style">
#repuestosSelectedList .zg-rep-code2.empty,
.zg-work-material-table td[data-label="Código"] b:empty{color:#9a5c00}
.zg-v56-code-pending{color:#9a5c00!important}
</style>

<?php require APP_ROOT . '/app/Views/tecnicos/partials/formulario_scripts.php'; ?>
