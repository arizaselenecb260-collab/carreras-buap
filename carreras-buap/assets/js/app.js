/* =====================================================================
   CARRERAS BUAP · app.js
   Interactividad del sitio y de la PWA. Los datos (fichas, materias,
   encuestas, usuarios) los guarda PHP en MySQL; aquí solo se mejora la
   experiencia: menú, modo oscuro, animaciones, validación en vivo,
   test vocacional, mapa corporal, galería, encuesta por pasos, etc.
   ===================================================================== */
(() => {
  "use strict";

  const $  = (s, r = document) => r.querySelector(s);
  const $$ = (s, r = document) => [...r.querySelectorAll(s)];
  const reducirMov = matchMedia("(prefers-reduced-motion: reduce)").matches;
  const guardar = (k, v) => { try { localStorage.setItem("carreras:" + k, v); } catch (_) {} };
  const leer = (k) => { try { return localStorage.getItem("carreras:" + k); } catch (_) { return null; } };

  /* ---------------- Notificaciones (toasts) ---------------- */
  const zonaToasts = $("#toasts");
  function cerrarToast(t) { t.classList.add("sale"); setTimeout(() => t.remove(), 350); }
  function toast(msg, tipo = "ok") {
    if (!zonaToasts) return;
    const t = document.createElement("div");
    t.className = "toast " + tipo;
    t.setAttribute("role", "status");
    t.textContent = msg;
    zonaToasts.appendChild(t);
    setTimeout(() => cerrarToast(t), 4500);
  }
  $$(".toast").forEach((t) => setTimeout(() => cerrarToast(t), 5500));

  /* ---------------- Menú y desplegables ---------------- */
  const btnMenu = $("#hamburguesa"), menu = $("#menu");
  if (btnMenu && menu) {
    btnMenu.addEventListener("click", () => {
      const abierto = menu.classList.toggle("abierto");
      btnMenu.setAttribute("aria-expanded", abierto);
      btnMenu.setAttribute("aria-label", abierto ? "Cerrar menú" : "Abrir menú");
    });
  }
  const cerrarDropdowns = (excepto) => $$(".dropdown.abierto").forEach((d) => {
    if (d !== excepto) { d.classList.remove("abierto"); $("button", d)?.setAttribute("aria-expanded", "false"); }
  });
  $$(".dropdown > button").forEach((b) => b.addEventListener("click", (ev) => {
    ev.stopPropagation();
    const li = b.parentElement;
    cerrarDropdowns(li);
    const abierto = li.classList.toggle("abierto");
    b.setAttribute("aria-expanded", abierto);
  }));
  document.addEventListener("click", (ev) => { if (!ev.target.closest(".dropdown")) cerrarDropdowns(); });
  document.addEventListener("keydown", (ev) => {
    if (ev.key === "Escape") {
      cerrarDropdowns();
      if (menu?.classList.contains("abierto")) btnMenu.click();
    }
  });
  // Cerrar el menú móvil al elegir un enlace
  $$("#menu a").forEach((a) => a.addEventListener("click", () => {
    if (menu.classList.contains("abierto")) btnMenu.click();
  }));

  /* ---------------- Modo claro / oscuro ---------------- */
  const btnTema = $("#tema");
  const metaColor = $('meta[name="theme-color"]');
  const colorBase = metaColor ? metaColor.getAttribute("content") : "#0B2340";
  const aplicarTema = (t) => {
    document.documentElement.dataset.tema = t;
    if (btnTema) {
      btnTema.textContent = t === "oscuro" ? "☀️" : "🌙";
      btnTema.setAttribute("aria-label", t === "oscuro" ? "Cambiar a modo claro" : "Cambiar a modo oscuro");
    }
    metaColor?.setAttribute("content", t === "oscuro" ? "#0B1A24" : colorBase);
  };
  aplicarTema(leer("tema") || (matchMedia("(prefers-color-scheme: dark)").matches ? "oscuro" : "claro"));
  btnTema?.addEventListener("click", () => {
    const nuevo = document.documentElement.dataset.tema === "oscuro" ? "claro" : "oscuro";
    aplicarTema(nuevo);
    guardar("tema", nuevo);
  });

  /* ---------------- Scroll: progreso, sombra, botón arriba ---------------- */
  const barra = $("#progreso"), nav = $("#nav"), arriba = $("#arriba");
  const alScroll = () => {
    const alto = document.documentElement.scrollHeight - innerHeight;
    if (barra) barra.style.width = (alto > 0 ? (scrollY / alto) * 100 : 0) + "%";
    nav?.classList.toggle("sombra", scrollY > 10);
    if (arriba) arriba.hidden = scrollY < 600;
  };
  addEventListener("scroll", alScroll, { passive: true });
  alScroll();
  arriba?.addEventListener("click", () => scrollTo({ top: 0, behavior: reducirMov ? "auto" : "smooth" }));

  /* ---------------- Animaciones al aparecer ---------------- */
  const animarContador = (el) => {
    const meta = +el.dataset.meta || 0;
    if (reducirMov || meta === 0) { el.textContent = meta.toLocaleString("es-MX"); return; }
    const ini = performance.now(), dur = 1500;
    const paso = (t) => {
      const p = Math.min((t - ini) / dur, 1);
      el.textContent = Math.round(meta * (1 - Math.pow(1 - p, 3))).toLocaleString("es-MX");
      if (p < 1) requestAnimationFrame(paso);
    };
    requestAnimationFrame(paso);
  };
  const objetivos = [...$$(".revelar"), ...$$(".contador"), ...$$(".barra-res i, .g-col i")];
  if ("IntersectionObserver" in window) {
    const obs = new IntersectionObserver((entradas) => entradas.forEach((en) => {
      if (!en.isIntersecting) return;
      const el = en.target;
      if (el.classList.contains("contador")) animarContador(el);
      else if (el.tagName === "I") el.classList.add("lleno");
      else el.classList.add("visible");
      obs.unobserve(el);
    }), { threshold: 0.15, rootMargin: "0px 0px -40px 0px" });
    objetivos.forEach((el) => obs.observe(el));
  } else {
    $$(".revelar").forEach((el) => el.classList.add("visible"));
    $$(".barra-res i, .g-col i").forEach((el) => el.classList.add("lleno"));
    $$(".contador").forEach(animarContador);
  }

  /* ---------------- PWA: service worker, instalar, sin conexión ---------------- */
  if ("serviceWorker" in navigator && location.protocol !== "file:") {
    addEventListener("load", () => navigator.serviceWorker.register("sw.js").catch(() => {}));
  }
  const btnInstalar = $("#instalar");
  let eventoInstalar = null;
  addEventListener("beforeinstallprompt", (ev) => { ev.preventDefault(); eventoInstalar = ev; if (btnInstalar) btnInstalar.hidden = false; });
  btnInstalar?.addEventListener("click", async () => {
    if (!eventoInstalar) return;
    eventoInstalar.prompt();
    const r = await eventoInstalar.userChoice;
    if (r.outcome === "accepted") toast("📲 ¡App instalada! Búscala en tu pantalla de inicio.");
    eventoInstalar = null;
    btnInstalar.hidden = true;
  });
  addEventListener("appinstalled", () => { if (btnInstalar) btnInstalar.hidden = true; });
  const avisoRed = $("#sinRed");
  const estadoRed = () => { if (avisoRed) avisoRed.hidden = navigator.onLine; };
  addEventListener("online", () => { estadoRed(); toast("✅ Conexión recuperada"); });
  addEventListener("offline", estadoRed);
  estadoRed();

  /* ---------------- Confirmar eliminación (ventana modal) ---------------- */
  const modal = $("#modalConfirmar");
  $$("form[data-confirmar]").forEach((f) => f.addEventListener("submit", (ev) => {
    if (f.dataset.confirmado) return;
    ev.preventDefault();
    const texto = f.dataset.confirmar;
    if (!modal || typeof modal.showModal !== "function") {
      if (confirm(texto)) { f.dataset.confirmado = "1"; f.submit(); }
      return;
    }
    $("#modalTexto").textContent = texto + " Esta acción no se puede deshacer.";
    modal.returnValue = "";
    modal.showModal();
    modal.addEventListener("close", function alCerrar() {
      modal.removeEventListener("close", alCerrar);
      if (modal.returnValue === "si") { f.dataset.confirmado = "1"; f.submit(); }
    });
  }));

  // Cambiar estado de ficha al elegir en la lista (administrador)
  $$("[data-auto-submit]").forEach((s) => s.addEventListener("change", () => s.form.submit()));

  /* ---------------- Inicio de sesión ---------------- */
  $$(".demo-btn").forEach((b) => b.addEventListener("click", () => {
    const f = b.closest("form");
    f.correo.value = b.dataset.correo;
    f.password.value = b.dataset.pass;
    f.password.focus();
    toast("Datos de prueba listos. Presiona «Entrar».", "aviso");
  }));
  $$("[data-ojo]").forEach((b) => b.addEventListener("click", () => {
    const inp = document.getElementById(b.dataset.ojo);
    const ver = inp.type === "password";
    inp.type = ver ? "text" : "password";
    b.textContent = ver ? "🙈" : "👁️";
    b.setAttribute("aria-label", ver ? "Ocultar contraseña" : "Mostrar contraseña");
  }));
  const pass1 = $("#pass1");
  if (pass1) pass1.addEventListener("input", () => {
    const v = pass1.value;
    let p = 0;
    if (v.length >= 8) p++;
    if (/[A-Z]/.test(v) && /[a-z]/.test(v)) p++;
    if (/\d/.test(v)) p++;
    if (/[^A-Za-z0-9]/.test(v) || v.length >= 12) p++;
    const niveles = [["Muy débil", "#D64545"], ["Débil", "#E8833A"], ["Aceptable", "#F5B731"], ["Buena", "#4CB878"], ["Excelente", "#00A896"]];
    const [txt, color] = niveles[p];
    $("#fuerzaBarra").style.cssText = `width:${(p / 4) * 100}%;background:${color}`;
    $("#fuerzaTexto").textContent = "Seguridad: " + txt;
  });

  /* ---------------- Formularios: validación en vivo ---------------- */
  $$("form.formulario").forEach((form) => {
    $$("input, select, textarea", form).forEach((c) => {
      c.addEventListener("blur", () => {
        if (!c.value || c.type === "range" || c.type === "radio" || c.type === "checkbox") return;
        const ok = c.checkValidity();
        c.classList.toggle("ok", ok);
        c.classList.toggle("mal", !ok);
      });
      c.addEventListener("input", () => { if (c.classList.contains("mal") && c.checkValidity()) { c.classList.remove("mal"); c.classList.add("ok"); c.removeAttribute("aria-invalid"); } });
    });
  });
  const formFicha = $("#formFicha");
  if (formFicha) {
    const curp = formFicha.curp, cuenta = $("#curpCuenta");
    const contar = () => { if (cuenta) cuenta.textContent = curp.value.length; };
    curp.addEventListener("input", () => { curp.value = curp.value.toUpperCase().replace(/[^A-Z0-9]/g, ""); contar(); });
    contar();
    formFicha.telefono.addEventListener("input", (ev) => { ev.target.value = ev.target.value.replace(/\D/g, "").slice(0, 10); });
    const rango = $("#promedio"), salida = $("#promSalida");
    const pintar = () => { salida.textContent = (+rango.value).toFixed(1); };
    rango?.addEventListener("input", pintar);
    if (rango) pintar();
    formFicha.addEventListener("submit", (ev) => {
      if (formFicha.checkValidity()) return;
      ev.preventDefault();
      const malos = $$(":invalid", formFicha).filter((c) => c.name);
      malos.forEach((c) => c.classList.add("mal"));
      malos[0]?.focus();
      toast("Revisa los campos marcados en rojo.", "error");
    });
  }

  /* ---------------- Plan de estudios: pestañas, búsqueda y filtro ---------------- */
  const tabs = $("#tabs"), paneles = $("#paneles");
  if (tabs && paneles) {
    let area = "todas";
    const activarTab = (b) => {
      $$(".tab", tabs).forEach((t) => { const on = t === b; t.classList.toggle("activo", on); t.setAttribute("aria-selected", on); });
      $$(".panel-sem", paneles).forEach((p) => p.classList.toggle("visible", p.dataset.sem === b.dataset.sem));
    };
    tabs.addEventListener("click", (ev) => { const b = ev.target.closest(".tab"); if (b) activarTab(b); });
    tabs.addEventListener("keydown", (ev) => {
      if (!["ArrowRight", "ArrowLeft"].includes(ev.key)) return;
      const lista = $$(".tab", tabs), i = lista.indexOf(document.activeElement);
      const sig = lista[(i + (ev.key === "ArrowRight" ? 1 : lista.length - 1)) % lista.length];
      sig.focus(); activarTab(sig);
    });
    const buscador = $("#buscaMateria");
    const normal = (s) => s.normalize("NFD").replace(/[̀-ͯ]/g, "").toLowerCase();
    const filtrar = () => {
      const q = normal(buscador.value.trim());
      const buscando = q !== "" || area !== "todas";
      paneles.classList.toggle("buscando", buscando);
      tabs.style.opacity = buscando ? ".4" : "1";
      $$(".panel-sem", paneles).forEach((p) => {
        let n = 0;
        $$("tbody tr[data-area]", p).forEach((tr) => {
          const ok = (!q || normal(tr.textContent).includes(q)) && (area === "todas" || tr.dataset.area === area);
          tr.classList.toggle("oculta", !ok);
          if (ok) n++;
        });
        p.classList.toggle("sin-res", n === 0);
      });
    };
    buscador?.addEventListener("input", filtrar);
    $("#filtrosArea")?.addEventListener("click", (ev) => {
      const b = ev.target.closest(".chip-filtro");
      if (!b) return;
      area = b.dataset.area;
      $$(".chip-filtro").forEach((c) => c.classList.toggle("activo", c === b));
      filtrar();
    });
  }

  /* ---------------- Test vocacional (inicio) ---------------- */
  const test = $("#testCuerpo");
  if (test) {
    let preguntas = [];
    try { preguntas = JSON.parse(test.dataset.preguntas); } catch (_) {}
    let paso = 0, puntos = 0;
    const pintarPaso = () => {
      if (paso >= preguntas.length) return resultado();
      const p = preguntas[paso];
      test.innerHTML = `<div class="test-paso">
        <div class="test-barra"><i style="width:${(paso / preguntas.length) * 100}%"></i></div>
        <small>Pregunta ${paso + 1} de ${preguntas.length}</small>
        <h3>${p.p}</h3>
        <div class="test-ops">${p.o.map((o, i) => `<button type="button" data-v="${2 - i}">${o}</button>`).join("")}</div></div>`;
    };
    const resultado = () => {
      const pct = Math.round((puntos / (preguntas.length * 2)) * 100);
      const [titulo, txt] = pct >= 75 ? ["¡Gran afinidad! 💚", "Tus respuestas encajan muy bien con la carrera. Haz el test completo para confirmarlo."]
        : pct >= 45 ? ["Tienes buena afinidad 👍", "Conoce el plan de estudios y el campo laboral para decidirte."]
        : ["Quizá te interese otra área 🤔", "Explora el sitio: tal vez descubras algo que no conocías de la fisioterapia."];
      test.innerHTML = `<div class="test-res"><div class="dona" style="--p:${pct}"><b>${pct}%</b></div>
        <h3>${titulo}</h3><p>${txt}</p>
        <div class="hero-btns centro"><a class="btn" href="plan.php">Ver plan de estudios</a><button type="button" class="btn btn-borde" data-repetir>Repetir test</button></div></div>`;
    };
    test.addEventListener("click", (ev) => {
      const b = ev.target.closest("button[data-v]");
      if (b) { puntos += +b.dataset.v; paso++; pintarPaso(); return; }
      if (ev.target.closest("[data-repetir]")) { paso = 0; puntos = 0; pintarPaso(); }
    });
    pintarPaso();
  }

  /* ---------------- Mapa corporal (campo laboral) ---------------- */
  const info = $("#cuerpoInfo");
  if (info) {
    const elegir = (z) => {
      $$(".zona").forEach((x) => x.classList.toggle("activa", x === z));
      info.innerHTML = `<h3>${z.dataset.t}</h3><p>${z.dataset.d}</p>`;
      info.classList.remove("cambia"); void info.offsetWidth; info.classList.add("cambia");
    };
    $$(".zona").forEach((z) => {
      z.setAttribute("role", "button");
      z.setAttribute("aria-label", "Fisioterapia " + z.dataset.t);
      z.addEventListener("click", () => elegir(z));
      z.addEventListener("keydown", (ev) => { if (ev.key === "Enter" || ev.key === " ") { ev.preventDefault(); elegir(z); } });
    });
  }

  /* ---------------- Embudo de marketing (campo laboral) ---------------- */
  const embInfo = $("#embudoInfo");
  if (embInfo) {
    $$(".etapa").forEach((b) => b.addEventListener("click", () => {
      $$(".etapa").forEach((x) => x.classList.toggle("activa", x === b));
      embInfo.innerHTML = `<h3>${b.dataset.t}</h3><p>${b.dataset.d}</p>
        <span class="chip-info">🛠️ ${b.dataset.h}</span> <span class="chip-info">👤 ${b.dataset.r}</span>`;
      embInfo.classList.remove("cambia"); void embInfo.offsetWidth; embInfo.classList.add("cambia");
    }));
  }

  /* ---------------- Calculadora de ROI ---------------- */
  const roiForm = $("#roiForm");
  if (roiForm) {
    const mx = (n, d = 0) => "$" + n.toLocaleString("es-MX", { minimumFractionDigits: d, maximumFractionDigits: d });
    const calc = () => {
      const inv = +$("#rInv").value, cpc = +$("#rCpc").value, conv = +$("#rConv").value, ticket = +$("#rTicket").value;
      $("#oInv").textContent = mx(inv);
      $("#oCpc").textContent = mx(cpc, 2);
      $("#oConv").textContent = conv + "%";
      $("#oTicket").textContent = mx(ticket);
      const clics = Math.floor(inv / cpc);
      const ventas = Math.floor(clics * conv / 100);
      const ingresos = ventas * ticket;
      const roi = Math.round(((ingresos - inv) / inv) * 100);
      $("#resClics").textContent = clics.toLocaleString("es-MX");
      $("#resVentas").textContent = ventas.toLocaleString("es-MX");
      $("#resIng").textContent = mx(ingresos);
      $("#resRoi").textContent = roi + "%";
      $("#roiTotal").classList.toggle("pierde", roi < 0);
      $("#resMsg").textContent = roi >= 100 ? "¡Campaña muy rentable! 🎉" : roi >= 0 ? "Recuperas lo invertido 👍" : "La campaña pierde dinero: ajusta CPC o conversión 🔧";
    };
    roiForm.addEventListener("input", calc);
    calc();
  }

  /* ---------------- Ficha: cambiar sedes según la carrera ---------------- */
  const elige = $("#eligeCarrera"), selSede = $("#selSede");
  if (elige && selSede) {
    elige.addEventListener("change", (ev) => {
      const r = ev.target;
      if (r.name !== "carrera_id") return;
      let sedes = [];
      try { sedes = JSON.parse(r.dataset.sedes); } catch (_) {}
      selSede.innerHTML = sedes.map((s) => `<option>${s}</option>`).join("");
      $("#etqSede").textContent = r.dataset.etiqueta;
      toast("Carrera seleccionada: " + r.closest("label").querySelector("b").textContent, "aviso");
    });
  }

  /* ---------------- Test vocacional (60 preguntas por bloques) ---------------- */
  const tv = $("#testVocacional");
  if (tv) {
    const total = +tv.dataset.total;
    const zonaTv = $("#tvPasos"), pasosTv = $$(".tv-paso", zonaTv);
    const barraTv = $("#tvBarra"), textoTv = $("#tvTexto"), puntosTv = $("#tvPuntos");
    const antTv = $("#tvAnterior"), sigTv = $("#tvSiguiente");
    const CLAVE = "test-progreso";
    let actualTv = 0;

    // Recuperar avance guardado en este navegador (por si se cerró la página)
    try {
      const guardado = JSON.parse(leer(CLAVE) || "{}");
      Object.entries(guardado).forEach(([n, v]) => {
        const inp = tv.querySelector(`input[name="${n}"][value="${v}"]`);
        if (inp && !tv.querySelector(`input[name="${n}"]:checked`)) inp.checked = true;
      });
    } catch (_) {}
    const guardarAvance = () => {
      const datos = {};
      $$("input[type=radio]:checked", tv).forEach((i) => { datos[i.name] = i.value; });
      guardar(CLAVE, JSON.stringify(datos));
    };

    const contestadas = () => $$("input[type=radio]:checked", tv).length;
    const faltanEn = (paso) => $$(".tv-preg", paso).filter((p) => !$("input:checked", p));
    puntosTv.innerHTML = pasosTv.map((_, k) => `<button type="button" aria-label="Ir al paso ${k + 1}" data-ir="${k}"></button>`).join("");

    const progresoTv = () => {
      const n = contestadas();
      barraTv.style.width = (n / total) * 100 + "%";
      textoTv.textContent = n + " / " + total;
      $$("button", puntosTv).forEach((b, k) => {
        const p = pasosTv[k];
        const hecho = k === 0 ? (tv.nombre.value.trim().length >= 2) : (p.classList.contains("final") ? n === total : faltanEn(p).length === 0);
        b.classList.toggle("hecho", hecho);
        b.classList.toggle("actual", k === actualTv);
      });
    };
    const irTv = (k, enfocar = true) => {
      actualTv = Math.max(0, Math.min(pasosTv.length - 1, k));
      pasosTv.forEach((p, j) => p.classList.toggle("actual", j === actualTv));
      antTv.style.visibility = actualTv === 0 ? "hidden" : "visible";
      sigTv.hidden = actualTv === pasosTv.length - 1;
      progresoTv();
      if (enfocar) tv.scrollIntoView({ behavior: reducirMov ? "auto" : "smooth", block: "start" });
    };
    const validarPaso = (k) => {
      const p = pasosTv[k];
      if (k === 0) {
        if (tv.nombre.value.trim().length < 2) { tv.nombre.classList.add("mal"); tv.nombre.focus(); toast("Escribe tu nombre para empezar 🙂", "aviso"); return false; }
        return true;
      }
      const faltan = faltanEn(p);
      $$(".tv-preg", p).forEach((q) => q.classList.toggle("falta", faltan.includes(q)));
      if (faltan.length) {
        toast(`Te faltan ${faltan.length} pregunta(s) en este bloque.`, "aviso");
        faltan[0].scrollIntoView({ behavior: reducirMov ? "auto" : "smooth", block: "center" });
        return false;
      }
      return true;
    };

    zonaTv.classList.add("pasos-on");
    $("#tvNav").hidden = false;
    // Empezar donde se quedó
    let inicio = 0;
    if (tv.nombre.value.trim().length >= 2) {
      inicio = pasosTv.findIndex((p, k) => k > 0 && !p.classList.contains("final") && faltanEn(p).length);
      if (inicio === -1) inicio = contestadas() === total ? pasosTv.length - 1 : 1;
      if (contestadas() === 0) inicio = 0;
    }
    irTv(inicio, false);
    if (contestadas() > 0 && contestadas() < total) toast(`Recuperamos tu avance: ${contestadas()} de ${total} respuestas.`, "aviso");

    sigTv.addEventListener("click", () => { if (validarPaso(actualTv)) irTv(actualTv + 1); });
    antTv.addEventListener("click", () => irTv(actualTv - 1));
    puntosTv.addEventListener("click", (ev) => { const b = ev.target.closest("[data-ir]"); if (b) irTv(+b.dataset.ir); });
    tv.addEventListener("change", (ev) => {
      if (ev.target.type === "radio") {
        ev.target.closest(".tv-preg")?.classList.remove("falta");
        guardarAvance();
      }
      progresoTv();
    });
    tv.nombre.addEventListener("input", progresoTv);
    tv.addEventListener("submit", (ev) => {
      for (let k = 0; k < pasosTv.length - 1; k++) {
        if (!validarPaso(k)) { ev.preventDefault(); irTv(k, false); return; }
      }
      guardar(CLAVE, "{}"); // ya no hace falta el respaldo local
    });
  }

  /* ---------------- Galería con visor (lightbox) ---------------- */
  const galeria = $("#galeria");
  if (galeria) {
    const items = $$("a", galeria);
    let i = 0, caja = null;
    const mostrar = () => {
      const a = items[i];
      $("img", caja).src = a.href;
      $("img", caja).alt = $("img", a).alt;
      $("p", caja).textContent = `${$("img", a).alt} · ${i + 1}/${items.length}`;
    };
    const cerrar = () => { caja?.remove(); caja = null; document.removeEventListener("keydown", teclas); items[i].focus(); };
    const mover = (d) => { i = (i + d + items.length) % items.length; mostrar(); };
    const teclas = (ev) => {
      if (ev.key === "Escape") cerrar();
      if (ev.key === "ArrowRight") mover(1);
      if (ev.key === "ArrowLeft") mover(-1);
    };
    items.forEach((a, k) => a.addEventListener("click", (ev) => {
      ev.preventDefault();
      i = k;
      caja = document.createElement("div");
      caja.className = "lightbox";
      caja.setAttribute("role", "dialog");
      caja.setAttribute("aria-label", "Visor de imágenes");
      caja.innerHTML = `<figure><img alt=""><p></p></figure>
        <button class="lb-cerrar" aria-label="Cerrar">✕</button><button class="lb-ant" aria-label="Anterior">‹</button><button class="lb-sig" aria-label="Siguiente">›</button>`;
      document.body.appendChild(caja);
      mostrar();
      $(".lb-cerrar", caja).focus();
      caja.addEventListener("click", (e) => {
        if (e.target === caja || e.target.closest(".lb-cerrar")) cerrar();
        else if (e.target.closest(".lb-ant")) mover(-1);
        else if (e.target.closest(".lb-sig")) mover(1);
      });
      document.addEventListener("keydown", teclas);
    }));
  }

  // Disco que gira mientras suena el spot
  $$(".reproductor audio").forEach((a) => {
    const disco = $(".disco", a.closest(".reproductor"));
    a.addEventListener("play", () => disco?.classList.add("gira"));
    ["pause", "ended"].forEach((e) => a.addEventListener(e, () => disco?.classList.remove("gira")));
  });

  /* ---------------- Encuesta por pasos ---------------- */
  const enc = $("#encuesta");
  if (enc) {
    const zona = $("#encPasos"), pasos = $$(".pregunta", zona), navEnc = $("#encNav");
    const preguntas = pasos.filter((p) => $("input[type=radio]", p));
    const barraEnc = $("#encBarra"), textoEnc = $("#encTexto"), puntos = $("#encPuntos");
    const btnAnt = $("#encAnterior"), btnSig = $("#encSiguiente");
    let actual = 0;

    const respondida = (p) => !!$("input:checked", p);
    puntos.innerHTML = pasos.map((_, k) => `<button type="button" aria-label="Ir a la pregunta ${k + 1}" data-ir="${k}"></button>`).join("");

    const progreso = () => {
      const n = preguntas.filter(respondida).length;
      barraEnc.style.width = n * 10 + "%";
      textoEnc.textContent = n + " / 10";
      $$("button", puntos).forEach((b, k) => {
        b.classList.toggle("hecho", !!pasos[k] && (respondida(pasos[k]) || (k === pasos.length - 1 && n === 10)));
        b.classList.toggle("actual", k === actual);
      });
    };
    const ir = (k) => {
      actual = Math.max(0, Math.min(pasos.length - 1, k));
      pasos.forEach((p, j) => p.classList.toggle("actual", j === actual));
      btnAnt.disabled = actual === 0;
      btnAnt.style.visibility = actual === 0 ? "hidden" : "visible";
      btnSig.hidden = actual === pasos.length - 1;
      progreso();
      const primerCampo = $("input:checked, input, textarea", pasos[actual]);
      if (document.activeElement && enc.contains(document.activeElement)) primerCampo?.focus({ preventScroll: true });
    };

    zona.classList.add("pasos-on");
    navEnc.hidden = false;
    const primeraVacia = preguntas.findIndex((p) => !respondida(p));
    ir(primeraVacia === -1 ? 0 : primeraVacia);

    btnSig.addEventListener("click", () => {
      if (pasos[actual] && preguntas.includes(pasos[actual]) && !respondida(pasos[actual])) {
        toast("Elige una opción para continuar 🙂", "aviso");
        pasos[actual].animate?.([{ transform: "translateX(0)" }, { transform: "translateX(-8px)" }, { transform: "translateX(8px)" }, { transform: "translateX(0)" }], { duration: 300 });
        return;
      }
      ir(actual + 1);
    });
    btnAnt.addEventListener("click", () => ir(actual - 1));
    puntos.addEventListener("click", (ev) => { const b = ev.target.closest("[data-ir]"); if (b) ir(+b.dataset.ir); });
    enc.addEventListener("change", (ev) => {
      if (ev.target.type !== "radio") return;
      progreso();
      if (!reducirMov) setTimeout(() => { if (pasos[actual].contains(ev.target)) ir(actual + 1); }, 380);
      else ir(actual + 1);
    });
    enc.addEventListener("submit", (ev) => {
      const falta = preguntas.findIndex((p) => !respondida(p));
      if (falta !== -1) {
        ev.preventDefault();
        toast(`Te falta responder la pregunta ${falta + 1}.`, "error");
        ir(falta);
      }
    });
    progreso();
  }
})();
