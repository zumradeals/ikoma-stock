<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Ikoma — Preview composants</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Manrope:wght@500;600;700;800&display=swap" rel="stylesheet">
<style>
/* ── Tokens (miroir de tailwind.config.js) ── */
:root {
  --brand:      #ea580c;
  --brand-dark: {{ brand_dark('#ea580c') }};
  --brand-wash: {{ brand_wash('#ea580c') }};
  --cream:      #FBF6F0;
  --ink:        #211D1A;
  --ink-soft:   #6B6259;
  --line:       #EAE0D4;
  --success:    #1F8A55;
  --success-wash:#E4F5EA;
  --gold:       #B9790A;
  --gold-wash:  #FBF0D9;
  --danger:     #C0392B;
  --danger-wash:#FBEAE7;
  --info:       #2454A8;
  --info-wash:  #E9F0FB;
  --charcoal:   #201F22;
}

*,*::before,*::after{box-sizing:border-box;margin:0;padding:0;}
body{font-family:'Manrope',sans-serif;background:#F1ECE4;color:var(--ink);padding:32px 20px 80px;}

/* ── Layout ── */
.wrap{max-width:900px;margin:0 auto;display:flex;flex-direction:column;gap:48px;}
h1{font-size:22px;font-weight:800;margin-bottom:4px;}
.lead{font-size:13.5px;color:var(--ink-soft);margin-bottom:0;}
.section-title{font-size:11px;font-weight:800;text-transform:uppercase;letter-spacing:.07em;color:var(--ink-soft);margin-bottom:14px;}
.card{background:#fff;border:1px solid var(--line);border-radius:20px;padding:24px;}
.card + .card{margin-top:16px;}
.row{display:flex;flex-wrap:wrap;gap:12px;}

/* ── Phones grid ── */
.phones{display:flex;gap:20px;flex-wrap:wrap;}
.phone-wrap{display:flex;flex-direction:column;gap:10px;}
.phone-label{font-size:11.5px;color:var(--ink-soft);font-weight:700;}
.phone{
  width:260px;background:var(--cream);border-radius:30px;border:8px solid var(--charcoal);
  box-shadow:0 18px 40px -14px rgba(30,20,10,.35);overflow:hidden;display:flex;flex-direction:column;
  position:relative;
}
.notch{position:absolute;top:0;left:50%;transform:translateX(-50%);width:90px;height:18px;background:var(--charcoal);border-radius:0 0 12px 12px;z-index:5;}
.phone-body{padding:26px 14px 0;display:flex;flex-direction:column;gap:10px;flex:1;}

/* ── Buttons ── */
.btn-primary{
  display:inline-flex;align-items:center;gap:11px;width:100%;
  border-radius:16px;padding:14px 15px;font-weight:800;font-size:14px;
  background:var(--brand);color:#fff;border:none;cursor:pointer;
  box-shadow:0 8px 16px -8px rgba(232,89,12,0.6);transition:filter .15s;
}
.btn-primary:hover{filter:brightness(.9);}
.btn-primary .ic{width:34px;height:34px;border-radius:10px;background:rgba(255,255,255,.22);display:flex;align-items:center;justify-content:center;font-size:16px;flex:none;}
.btn-primary .chev{margin-left:auto;opacity:.5;font-size:13px;}

.btn-secondary{
  display:inline-flex;align-items:center;gap:11px;width:100%;
  border-radius:16px;padding:14px 15px;font-weight:800;font-size:14px;
  background:#fff;color:var(--ink);border:1.5px solid var(--line);cursor:pointer;transition:border-color .15s;
}
.btn-secondary:hover{border-color:var(--brand);}
.btn-secondary .ic{width:34px;height:34px;border-radius:10px;background:var(--brand-wash);display:flex;align-items:center;justify-content:center;font-size:16px;flex:none;}
.btn-secondary .chev{margin-left:auto;opacity:.3;font-size:13px;}

/* ── Option cards ── */
.opt-card{
  display:flex;align-items:center;gap:12px;
  border:2px solid var(--line);border-radius:15px;padding:13px 14px;font-weight:800;font-size:13.5px;
  background:#fff;cursor:pointer;transition:border-color .15s,background .15s;
}
.opt-card.sel{border-color:var(--brand);background:var(--brand-wash);}
.opt-card .radio{width:19px;height:19px;border-radius:50%;border:2px solid var(--line);flex:none;display:flex;align-items:center;justify-content:center;}
.opt-card.sel .radio{border-color:var(--brand);}
.radio-dot{width:9px;height:9px;border-radius:50%;background:var(--brand);}

/* ── Status badges ── */
.badge{display:inline-flex;align-items:center;gap:5px;border-radius:99px;padding:4px 10px;font-size:11px;font-weight:800;}
.badge.paid_delivered{background:var(--success-wash);color:var(--success);}
.badge.to_deliver    {background:var(--info-wash);color:var(--info);}
.badge.partial       {background:var(--gold-wash);color:var(--gold);}
.badge.free          {background:var(--info-wash);color:var(--info);}
.badge.unpaid        {background:var(--gold-wash);color:var(--gold);}
.badge.cancelled     {background:var(--danger-wash);color:var(--danger);}

/* ── Sale card ── */
.sale-card{background:#fff;border:1px solid var(--line);border-radius:14px;padding:12px 13px;display:flex;flex-direction:column;gap:7px;}
.sale-top{display:flex;justify-content:space-between;align-items:flex-start;gap:8px;}
.sale-desc{font-size:12.5px;font-weight:700;line-height:1.4;}
.sale-desc .muted{color:var(--ink-soft);font-weight:600;}
.sale-amt{font-weight:800;font-size:14px;white-space:nowrap;}

/* ── Bottom nav ── */
.bottom-nav{display:flex;justify-content:space-around;border-top:1px solid var(--line);background:#fff;padding:10px 6px 16px;}
.nav-item{display:flex;flex-direction:column;align-items:center;gap:3px;font-size:10px;font-weight:800;color:var(--ink-soft);background:none;border:none;cursor:pointer;}
.nav-item.active{color:var(--brand);}
.nav-item .ic{font-size:16px;}

/* ── Token swatch ── */
.swatches{display:flex;flex-wrap:wrap;gap:8px;}
.sw{display:flex;align-items:center;gap:7px;font-size:11.5px;font-weight:700;color:var(--ink-soft);background:var(--cream);border:1px solid var(--line);padding:5px 10px 5px 5px;border-radius:99px;}
.sw i{width:18px;height:18px;border-radius:5px;display:block;}
</style>
</head>
<body>
<div class="wrap">

  <!-- ── En-tête ── -->
  <div>
    <h1>Ikoma — Preview composants étape 2</h1>
    <p class="lead">Tokens actifs : <code>--brand={{ brand_dark('#ea580c') !== '#ea580c' ? '#ea580c' : '#ea580c' }}</code> / dark=<code>{{ brand_dark('#ea580c') }}</code> / wash=<code>{{ brand_wash('#ea580c') }}</code></p>
  </div>

  <!-- ── Tokens ── -->
  <div>
    <p class="section-title">Jetons de couleur actifs</p>
    <div class="swatches">
      <span class="sw"><i style="background:var(--brand)"></i>brand</span>
      <span class="sw"><i style="background:var(--brand-dark)"></i>brand-dark</span>
      <span class="sw"><i style="background:var(--brand-wash);border:1px solid var(--line)"></i>brand-wash</span>
      <span class="sw"><i style="background:var(--success)"></i>success</span>
      <span class="sw"><i style="background:var(--gold)"></i>gold</span>
      <span class="sw"><i style="background:var(--info)"></i>info</span>
      <span class="sw"><i style="background:var(--danger)"></i>danger</span>
      <span class="sw"><i style="background:var(--charcoal)"></i>charcoal</span>
      <span class="sw"><i style="background:var(--ink)"></i>ink</span>
      <span class="sw"><i style="background:var(--ink-soft)"></i>ink-soft</span>
      <span class="sw"><i style="background:var(--cream);border:1px solid var(--line)"></i>cream</span>
      <span class="sw"><i style="background:var(--line)"></i>line</span>
    </div>
  </div>

  <!-- ── Phones ── -->
  <div>
    <p class="section-title">Composants dans leur contexte mobile</p>
    <div class="phones">

      <!-- Phone 1 — Accueil (buttons + bottom-nav) -->
      <div class="phone-wrap">
        <div class="phone">
          <div class="notch"></div>
          <div class="phone-body">
            <div style="height:8px;"></div>
            <p style="font-size:11px;font-weight:800;color:var(--ink-soft);text-transform:uppercase;letter-spacing:.05em;">x-ikoma.button-primary / secondary</p>
            <button class="btn-primary"><span class="ic">🛒</span>Vendre<span class="chev">›</span></button>
            <button class="btn-secondary"><span class="ic">💰</span>Encaisser un paiement<span class="chev">›</span></button>
            <button class="btn-secondary"><span class="ic">📦</span>Livrer un client<span class="chev">›</span></button>
          </div>
          <div class="bottom-nav" style="margin-top:auto;">
            <button class="nav-item active"><span class="ic">🏠</span>Accueil</button>
            <button class="nav-item"><span class="ic">🛒</span>Vendre</button>
            <button class="nav-item"><span class="ic">💰</span>Paiements</button>
            <button class="nav-item"><span class="ic">👥</span>Clients</button>
          </div>
        </div>
        <p class="phone-label">Accueil — boutons + nav (active=home)</p>
      </div>

      <!-- Phone 2 — Option cards -->
      <div class="phone-wrap">
        <div class="phone">
          <div class="notch"></div>
          <div class="phone-body">
            <div style="height:8px;"></div>
            <p style="font-size:11px;font-weight:800;color:var(--ink-soft);text-transform:uppercase;letter-spacing:.05em;">x-ikoma.option-card</p>
            <p style="font-size:18px;font-weight:800;line-height:1.25;">Le client paie comment ?</p>
            <div class="opt-card sel">
              <span class="radio"><span class="radio-dot"></span></span>
              💵 Tout maintenant, espèces
            </div>
            <div class="opt-card">
              <span class="radio"></span>
              📱 Tout maintenant, Mobile Money
            </div>
            <div class="opt-card">
              <span class="radio"></span>
              🤝 Il paiera plus tard
            </div>
          </div>
          <div class="bottom-nav" style="margin-top:auto;">
            <button class="nav-item"><span class="ic">🏠</span>Accueil</button>
            <button class="nav-item active"><span class="ic">🛒</span>Vendre</button>
            <button class="nav-item"><span class="ic">💰</span>Paiements</button>
            <button class="nav-item"><span class="ic">👥</span>Clients</button>
          </div>
        </div>
        <p class="phone-label">Option cards — 1 sélectionnée (selected=true)</p>
      </div>

      <!-- Phone 3 — Status badges dans des sale-cards -->
      <div class="phone-wrap">
        <div class="phone">
          <div class="notch"></div>
          <div class="phone-body">
            <div style="height:8px;"></div>
            <p style="font-size:11px;font-weight:800;color:var(--ink-soft);text-transform:uppercase;letter-spacing:.05em;">x-ikoma.status-badge</p>
            <div class="sale-card">
              <div class="sale-top">
                <div class="sale-desc">Aujourd'hui à 16h08<br><span class="muted">Client de passage</span></div>
                <div class="sale-amt">50 000 F</div>
              </div>
              <span class="badge paid_delivered">✅ Payé et livré</span>
            </div>
            <div class="sale-card">
              <div class="sale-top">
                <div class="sale-desc">Aujourd'hui à 15h40<br><span class="muted">Awa Koné</span></div>
                <div class="sale-amt">75 000 F</div>
              </div>
              <span class="badge partial">💰 Reste à payer</span>
            </div>
            <div class="sale-card">
              <div class="sale-top">
                <div class="sale-desc">Hier à 11h02<br><span class="muted">Client de passage</span></div>
                <div class="sale-amt">25 000 F</div>
              </div>
              <span class="badge free">🎁 Offert</span>
            </div>
            <div class="sale-card">
              <div class="sale-top">
                <div class="sale-desc">Lundi à 09h15<br><span class="muted">Kofi Asante</span></div>
                <div class="sale-amt">12 000 F</div>
              </div>
              <span class="badge to_deliver">📦 Payé — à livrer</span>
            </div>
            <div class="sale-card">
              <div class="sale-top">
                <div class="sale-desc">Dimanche à 14h00<br><span class="muted">Client de passage</span></div>
                <div class="sale-amt">30 000 F</div>
              </div>
              <span class="badge cancelled">❌ Annulée</span>
            </div>
          </div>
          <div class="bottom-nav" style="margin-top:auto;">
            <button class="nav-item"><span class="ic">🏠</span>Accueil</button>
            <button class="nav-item active"><span class="ic">🛒</span>Vendre</button>
            <button class="nav-item"><span class="ic">💰</span>Paiements</button>
            <button class="nav-item"><span class="ic">👥</span>Clients</button>
          </div>
        </div>
        <p class="phone-label">Tous les statuts — historique ventes</p>
      </div>

    </div>
  </div>

  <!-- ── Tableau des 6 statuts ── -->
  <div>
    <p class="section-title">x-ikoma.status-badge — les 6 variantes</p>
    <div class="card">
      <div class="row" style="gap:8px;">
        <span class="badge paid_delivered">✅ Payé et livré</span>
        <span class="badge to_deliver">📦 Payé — à livrer</span>
        <span class="badge partial">💰 Reste à payer</span>
        <span class="badge free">🎁 Offert</span>
        <span class="badge unpaid">⏳ Non payé</span>
        <span class="badge cancelled">❌ Annulée</span>
      </div>
    </div>
  </div>

</div>
</body>
</html>
