// UniSmart Pay - Web Serial Monitor (reads ESP32 Serial over USB in Chrome/Edge)
// Requirements: HTTPS or http://localhost, and a supported browser (Chrome/Edge desktop).

(function () {
  function buildHints(err) {
    const msg = String((err && err.message) ? err.message : err || '');
    const name = String((err && err.name) ? err.name : '');

    // Common Windows/ESP32 causes when open() fails.
    const hints = [];
    if (/Failed to open serial port/i.test(msg) || /NetworkError/i.test(name) || /NetworkError/i.test(msg)) {
      hints.push('Ferme Arduino IDE (Serial Monitor/Plotter) et tout autre app qui utilise le port COM.');
      hints.push('Débranche/rebranche l\'ESP32 puis réessaie.');
      hints.push('Vérifie le bon port COM dans le Gestionnaire de périphériques.');
      hints.push('Installe le driver USB-Serial (CP210x / CH340) si nécessaire.');
    }
    if (/NotFoundError/i.test(name) || /No port selected/i.test(msg)) {
      hints.push('Aucun port choisi: clique Connecter puis sélectionne le COM de l\'ESP32.');
    }
    if (!hints.length) {
      hints.push('Si ça échoue: ferme les applis série, débranche/rebranche, puis réessaie.');
    }
    return hints;
  }

  function UniSmartWebSerialMonitor(options) {
    const state = {
      baudRate: options.baudRate || 115200,
      preEl: options.preEl,
      statusEl: options.statusEl,
      connectBtn: options.connectBtn,
      disconnectBtn: options.disconnectBtn,
      maxLines: options.maxLines || 400,
      port: null,
      reader: null,
      keepReading: false,
      buffer: '',
      lines: [],
    };

    function setStatus(msg) {
      if (state.statusEl) state.statusEl.textContent = String(msg || '');
    }

    function parseType(line) {
      const s = String(line || '');
      if (/^\[WEB\]/i.test(s)) return 'SYSTEME';
      const m = s.match(/^\[[^\]]+\]\s*\[([^\]]+)\]/);
      if (m && m[1]) return String(m[1]).trim().toUpperCase();
      const m2 = s.match(/^\[([^\]]+)\]/);
      if (m2 && m2[1]) return String(m2[1]).trim().toUpperCase();
      // Fallback: detect common keywords
      if (/\bSUCCESS\b/i.test(s) || /valid[ée] ✔/i.test(s) || /accept[ée] ✔/i.test(s)) return 'SUCCESS';
      if (/\bERROR\b/i.test(s) || /\bERREUR\b/i.test(s) || /refus[ée]/i.test(s)) return 'ERROR';
      if (/\bRFID\b/i.test(s)) return 'RFID';
      if (/\bPAIEMENT\b/i.test(s)) return 'PAIEMENT';
      if (/\bSCAN\b/i.test(s)) return 'SCAN';
      return 'INFO';
    }

    function typeColor(type) {
      switch (String(type || '').toUpperCase()) {
        case 'SUCCESS':
        case 'SOLDE':
          return 'var(--success)';
        case 'ERROR':
        case 'ERREUR':
          return 'var(--danger)';
        case 'WARNING':
          return 'var(--warning)';
        case 'RFID':
        case 'SCAN':
        case 'PANIER':
        case 'PAIEMENT':
          return 'var(--primary)';
        case 'RESPONSE':
          return 'var(--muted)';
        case 'USER':
        case 'RESTO':
          return 'var(--text)';
        case 'SYSTEME':
        case 'CONNEXION':
          return 'var(--muted)';
        default:
          return 'var(--muted)';
      }
    }

    function render() {
      if (!state.preEl) return;
      // Re-render as DOM nodes (safe: uses textContent)
      state.preEl.textContent = '';
      const frag = document.createDocumentFragment();
      for (const it of state.lines) {
        const full = String(it.text || '');
        const t = String(it.type || 'INFO').toUpperCase();
        const m1 = full.match(/^\[([^\]]+)\]\s*\[([^\]]+)\]\s*(.*)$/);
        const m2 = full.match(/^\[([^\]]+)\]\s*(.*)$/);

        if (m1) {
          const timeSpan = document.createElement('span');
          timeSpan.textContent = '[' + m1[1] + '] ';
          timeSpan.style.color = 'var(--muted)';
          frag.appendChild(timeSpan);

          const typeSpan = document.createElement('span');
          typeSpan.textContent = '[' + m1[2] + '] ';
          typeSpan.style.color = typeColor(t);
          typeSpan.style.fontWeight = '800';
          frag.appendChild(typeSpan);

          const msgSpan = document.createElement('span');
          msgSpan.textContent = m1[3] || '';
          msgSpan.style.color = typeColor(t);
          if (t === 'SUCCESS' || t === 'ERROR' || t === 'ERREUR') msgSpan.style.fontWeight = '700';
          frag.appendChild(msgSpan);
        } else if (m2) {
          const typeSpan = document.createElement('span');
          typeSpan.textContent = '[' + m2[1] + '] ';
          typeSpan.style.color = typeColor(t);
          typeSpan.style.fontWeight = '800';
          frag.appendChild(typeSpan);

          const msgSpan = document.createElement('span');
          msgSpan.textContent = m2[2] || '';
          msgSpan.style.color = typeColor(t);
          if (t === 'SUCCESS' || t === 'ERROR' || t === 'ERREUR') msgSpan.style.fontWeight = '700';
          frag.appendChild(msgSpan);
        } else {
          const span = document.createElement('span');
          span.textContent = full;
          span.style.color = typeColor(t);
          frag.appendChild(span);
        }
        frag.appendChild(document.createTextNode('\n'));
      }
      state.preEl.appendChild(frag);
      state.preEl.scrollTop = state.preEl.scrollHeight;
    }

    function appendLine(line) {
      const l = String(line ?? '').replace(/\r/g, '');
      if (!l) return;
      const t = parseType(l);
      state.lines.push({ text: l, type: t });
      if (state.lines.length > state.maxLines) {
        state.lines.splice(0, state.lines.length - state.maxLines);
      }
      render();
    }

    async function disconnect() {
      state.keepReading = false;
      try {
        if (state.reader) {
          try { await state.reader.cancel(); } catch (_) {}
          try { state.reader.releaseLock(); } catch (_) {}
        }
      } catch (_) {}
      state.reader = null;

      try {
        if (state.port) {
          try { await state.port.close(); } catch (_) {}
        }
      } catch (_) {}
      state.port = null;

      if (state.disconnectBtn) state.disconnectBtn.disabled = true;
      if (state.connectBtn) state.connectBtn.disabled = false;
      setStatus('Serial: déconnecté');
    }

    async function readLoop() {
      if (!state.port || !state.port.readable) return;

      const textDecoder = new TextDecoderStream();
      const readableStreamClosed = state.port.readable.pipeTo(textDecoder.writable).catch(() => {});
      const reader = textDecoder.readable.getReader();
      state.reader = reader;
      state.keepReading = true;

      setStatus('Serial: connecté (lecture en cours)');

      try {
        while (state.keepReading) {
          const { value, done } = await reader.read();
          if (done) break;
          if (value) {
            state.buffer += value;
            let idx;
            while ((idx = state.buffer.indexOf('\n')) >= 0) {
              const line = state.buffer.slice(0, idx).trimEnd();
              state.buffer = state.buffer.slice(idx + 1);
              if (line) appendLine(line);
            }
          }
        }
      } catch (e) {
        appendLine('[WEB] Erreur lecture serial: ' + (e && e.message ? e.message : String(e)));
      } finally {
        try { reader.releaseLock(); } catch (_) {}
        try { await readableStreamClosed; } catch (_) {}
      }

      await disconnect();
    }

    async function connect() {
      if (!('serial' in navigator)) {
        setStatus('Serial: non supporté (utilise Chrome/Edge desktop)');
        return;
      }

      const host = window.location.hostname;
      const isLocal = host === 'localhost' || host === '127.0.0.1';
      if (!window.isSecureContext && !isLocal) {
        setStatus('Serial: nécessite HTTPS ou localhost');
        return;
      }

      try {
        setStatus('Serial: demande permission…');
        const port = await navigator.serial.requestPort();
        state.port = port;

        // Try to open the port. It will fail if another app is using it.
        await port.open({ baudRate: state.baudRate });

        if (state.connectBtn) state.connectBtn.disabled = true;
        if (state.disconnectBtn) state.disconnectBtn.disabled = false;

        appendLine('[WEB] Connecté au port série (' + state.baudRate + ' baud)');
        readLoop();
      } catch (e) {
        setStatus('Serial: connexion échouée');
        const em = (e && e.message) ? e.message : String(e);
        appendLine('[WEB] Connexion échouée: ' + em);
        const hints = buildHints(e);
        for (const h of hints) {
          appendLine('[WEB] Hint: ' + h);
        }
        await disconnect();
      }
    }

    function init() {
      if (!state.preEl) throw new Error('preEl requis');
      if (state.connectBtn) state.connectBtn.addEventListener('click', connect);
      if (state.disconnectBtn) {
        state.disconnectBtn.addEventListener('click', disconnect);
        state.disconnectBtn.disabled = true;
      }
      setStatus('Serial: prêt');

      // Auto-cleanup
      window.addEventListener('beforeunload', () => {
        disconnect();
      });
    }

    return { init, connect, disconnect };
  }

  window.UniSmartWebSerialMonitor = UniSmartWebSerialMonitor;
})();
