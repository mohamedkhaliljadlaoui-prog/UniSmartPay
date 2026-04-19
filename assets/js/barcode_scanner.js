// UniSmart Pay - Barcode scanner helper (QuaggaJS)

function UniSmartBarcodeScanner(options) {
  const state = {
    onDetected: options.onDetected,
    onError: options.onError,
    onStatus: options.onStatus,
    preferNative: options.preferNative !== false,
    preferZXing: options.preferZXing !== false,
    allowQuagga: options.allowQuagga === true,
    allowedFormats: Array.isArray(options.allowedFormats) ? options.allowedFormats.map(String) : null,
    zxingTryHarder: options.zxingTryHarder === true,
    zxingAlsoInverted: options.zxingAlsoInverted === true,
    quaggaArea: (options.quaggaArea && typeof options.quaggaArea === 'object') ? options.quaggaArea : null,
    cooldownMs: options.cooldownMs || 1000,
    lastCode: null,
    lastTime: 0,
    lastDetectedAt: 0,
    initialized: false,
    running: false,
    targetId: null,
    // Native BarcodeDetector mode
    native: {
      enabled: false,
      detector: null,
      stream: null,
      video: null,
      canvas: null,
      rafId: 0,
      scanning: false,
    },
    // ZXing (JS) mode
    zxing: {
      enabled: false,
      reader: null,
      video: null,
      lastErrAt: 0,
      fallbackTimer: 0,
      fallbackTried: false,
    },
  };

  function normalizeAllowedFormats() {
    if (!state.allowedFormats || state.allowedFormats.length === 0) return null;
    const set = new Set(state.allowedFormats.map((s) => String(s).trim().toUpperCase()).filter(Boolean));
    return Array.from(set);
  }

  function nativeFormatsFromAllowed(allowed) {
    if (!allowed) return null;
    const map = {
      EAN_13: 'ean_13',
      EAN13: 'ean_13',
      EAN_8: 'ean_8',
      EAN8: 'ean_8',
      CODE_128: 'code_128',
      CODE128: 'code_128',
      CODE_39: 'code_39',
      CODE39: 'code_39',
      UPC_A: 'upc_a',
      UPC_E: 'upc_e',
      ITF: 'itf',
      CODABAR: 'codabar',
      PDF417: 'pdf417',
      PDF_417: 'pdf417',
      QR_CODE: 'qr_code',
      QRCODE: 'qr_code',
      DATA_MATRIX: 'data_matrix',
      DATAMATRIX: 'data_matrix',
      AZTEC: 'aztec',
    };
    const out = [];
    for (const f of allowed) {
      const v = map[String(f).toUpperCase()];
      if (v) out.push(v);
    }
    return out.length ? Array.from(new Set(out)) : null;
  }

  function quaggaReadersFromAllowed(allowed) {
    if (!allowed) return null;
    const map = {
      EAN_13: 'ean_reader',
      EAN13: 'ean_reader',
      EAN_8: 'ean_8_reader',
      EAN8: 'ean_8_reader',
      CODE_128: 'code_128_reader',
      CODE128: 'code_128_reader',
      CODE_39: 'code_39_reader',
      CODE39: 'code_39_reader',
      UPC_A: 'upc_reader',
      UPC_E: 'upc_e_reader',
      ITF: 'i2of5_reader',
      CODABAR: 'codabar_reader',
    };
    const out = [];
    for (const f of allowed) {
      const v = map[String(f).toUpperCase()];
      if (v) out.push(v);
    }
    return out.length ? Array.from(new Set(out)) : null;
  }

  function setStatus(message) {
    if (state.onStatus) {
      try {
        state.onStatus(String(message));
      } catch (_) {
        // ignore
      }
    }
  }

  function canUseNativeDetector() {
    return (
      state.preferNative &&
      typeof window !== 'undefined' &&
      'BarcodeDetector' in window &&
      navigator.mediaDevices &&
      typeof navigator.mediaDevices.getUserMedia === 'function'
    );
  }

  function canUseZXing() {
    return (
      state.preferZXing &&
      typeof window !== 'undefined' &&
      typeof window.ZXing !== 'undefined' &&
      navigator.mediaDevices &&
      typeof navigator.mediaDevices.getUserMedia === 'function'
    );
  }

  function nowMs() {
    return Date.now();
  }

  function shouldCooldown(code) {
    const now = nowMs();
    if (state.lastCode === code && now - state.lastTime < state.cooldownMs) {
      return true;
    }
    state.lastCode = code;
    state.lastTime = now;
    state.lastDetectedAt = now;
    return false;
  }

  function startQuagga(target) {
    if (typeof Quagga === 'undefined') {
      const err = new Error('QuaggaJS non chargé. Vérifie que /assets/vendor/quagga.min.js est accessible.');
      if (state.onError) state.onError(err);
      return;
    }
    setStatus('Caméra active (Quagga) — scanne…');

    const pickPreferredVideoDeviceId = async () => {
      try {
        if (!navigator.mediaDevices || typeof navigator.mediaDevices.enumerateDevices !== 'function') return null;
        const devices = await navigator.mediaDevices.enumerateDevices();
        const cams = (devices || []).filter((d) => d && d.kind === 'videoinput');
        if (cams.length === 0) return null;

        const withLabels = cams.filter((c) => typeof c.label === 'string' && c.label.trim() !== '');
        const preferred = (withLabels.length ? withLabels : cams).find((c) => {
          const label = String(c.label || '').toLowerCase();
          return label.includes('back') || label.includes('rear') || label.includes('environment');
        });
        return (preferred || cams[cams.length - 1]).deviceId || null;
      } catch (_) {
        return null;
      }
    };

    const allowed = normalizeAllowedFormats();
    const readersPreferred = quaggaReadersFromAllowed(allowed);
    const readers = (readersPreferred && readersPreferred.length)
      ? readersPreferred
      : [
          'ean_reader',
          'ean_8_reader',
          'code_128_reader',
          'code_39_reader',
          'upc_reader',
          'upc_e_reader',
          'codabar_reader',
          'i2of5_reader',
        ];

    if (!state.initialized) {
      pickPreferredVideoDeviceId()
        .then((preferredDeviceId) => {
          try {
            Quagga.init(
              {
                // Disable workers: fixes "Cannot read properties of undefined (reading 'data')" on some Chrome builds.
                numOfWorkers: 0,
                inputStream: {
                  type: 'LiveStream',
                  target,
                  constraints: {
                    facingMode: 'environment',
                    width: { ideal: 1920 },
                    height: { ideal: 1080 },
                    ...(preferredDeviceId ? { deviceId: { exact: preferredDeviceId } } : {}),
                  },
                  ...(state.quaggaArea ? { area: state.quaggaArea } : {}),
                },
                locator: {
                  halfSample: true,
                  patchSize: 'medium',
                },
                decoder: {
                  readers,
                },
                locate: true,
              },
              function (err) {
                if (err) {
                  console.error(err);
                  state.running = false;
                  state.initialized = false;
                  if (state.onError) state.onError(err);
                  return;
                }
                state.initialized = true;
                state.running = true;
                try {
                  Quagga.start();
                } catch (e) {
                  console.error(e);
                  state.running = false;
                  state.initialized = false;
                  if (state.onError) state.onError(e);
                }
              }
            );
          } catch (e) {
            console.error(e);
            state.running = false;
            state.initialized = false;
            if (state.onError) state.onError(e);
            return;
          }
        })
        .catch((e) => {
          state.running = false;
          state.initialized = false;
          if (state.onError) state.onError(e);
        });

      Quagga.onDetected((result) => {
        if (!state.running) return;
        const code = result?.codeResult?.code;
        if (!code) return;
        if (shouldCooldown(code)) return;
        setStatus('Code détecté: ' + String(code));
        state.onDetected(code);
      });
    } else {
      state.running = true;
      try {
        Quagga.start();
      } catch (err) {
        state.running = false;
        if (state.onError) state.onError(err);
      }
    }
  }

  async function startNative(target) {
    state.native.enabled = true;
    state.running = true;
    setStatus('Caméra active (BarcodeDetector) — scanne…');

    // Minimal UI: render a <video> inside the target container
    target.innerHTML = '';
    const video = document.createElement('video');
    video.setAttribute('playsinline', 'true');
    video.muted = true;
    video.autoplay = true;
    video.style.width = '100%';
    video.style.height = '100%';
    video.style.objectFit = 'contain';
    video.style.background = 'var(--bg)';
    target.appendChild(video);

    const canvas = document.createElement('canvas');
    const ctx = canvas.getContext('2d', { willReadFrequently: true });
    if (!ctx) {
      throw new Error('Canvas non disponible pour le scan.');
    }

    // Request camera
    setStatus('Demande accès caméra…');
    const stream = await navigator.mediaDevices.getUserMedia({
      video: { facingMode: { ideal: 'environment' } },
      audio: false,
    });
    video.srcObject = stream;

    const allowed = normalizeAllowedFormats();

    // Expand formats (support 1D + 2D used in the field)
    let formats = [
      'ean_13',
      'ean_8',
      'upc_a',
      'upc_e',
      'code_128',
      'code_39',
      'code_93',
      'itf',
      'codabar',
      'pdf417',
      'qr_code',
      'data_matrix',
      'aztec',
    ];

    const preferredFormats = nativeFormatsFromAllowed(allowed);
    if (preferredFormats && preferredFormats.length) {
      formats = preferredFormats;
    }
    try {
      if (typeof window.BarcodeDetector.getSupportedFormats === 'function') {
        const supported = await window.BarcodeDetector.getSupportedFormats();
        if (Array.isArray(supported) && supported.length) {
          formats = formats.filter((f) => supported.includes(f));
        }
      }
    } catch (_) {
      // ignore
    }

    const detector = new window.BarcodeDetector({ formats });

    state.native.detector = detector;
    state.native.stream = stream;
    state.native.video = video;
    state.native.canvas = canvas;
    state.native.scanning = true;

    await video.play();
    setStatus('Caméra active (BarcodeDetector) — scanne…');

    const scanLoop = async () => {
      if (!state.running || !state.native.scanning) return;
      if (video.readyState >= 2) {
        const w = video.videoWidth || 0;
        const h = video.videoHeight || 0;
        if (w > 0 && h > 0) {
          canvas.width = w;
          canvas.height = h;
          ctx.drawImage(video, 0, 0, w, h);
          try {
            const barcodes = await detector.detect(canvas);
            if (Array.isArray(barcodes) && barcodes.length > 0) {
              const code = barcodes[0]?.rawValue;
              if (code && !shouldCooldown(code)) {
                state.onDetected(code);
              }
            }
          } catch (e) {
            // Some devices may throw intermittently; surface once then continue
            if (state.onError) state.onError(e);
          }
        }
      }
      state.native.rafId = window.requestAnimationFrame(() => {
        scanLoop().catch(() => {});
      });
    };

    state.native.rafId = window.requestAnimationFrame(() => {
      scanLoop().catch(() => {});
    });
  }

  function startZXing(target) {
    state.zxing.enabled = true;
    state.running = true;
    setStatus('Caméra (ZXing) — initialisation…');

    target.innerHTML = '';
    const video = document.createElement('video');
    video.setAttribute('playsinline', 'true');
    video.muted = true;
    video.autoplay = true;
    video.style.width = '100%';
    video.style.height = '100%';
    video.style.objectFit = 'contain';
    video.style.background = 'var(--bg)';
    target.appendChild(video);

    const ZX = window.ZXing;
    const ReaderCtor = ZX?.BrowserMultiFormatReader;
    if (!ReaderCtor) {
      throw new Error('ZXing non disponible: BrowserMultiFormatReader manquant.');
    }

    // Provide hints when available to speed up and improve reliability.
    let reader;
    try {
      if (ZX?.DecodeHintType && ZX?.BarcodeFormat) {
        const hints = new Map();
          const zxingFormats = [];
          const allow = normalizeAllowedFormats();
          const add = (fmt) => zxingFormats.push(fmt);

          if (!allow || allow.includes('EAN_13')) add(ZX.BarcodeFormat.EAN_13);
          if (!allow || allow.includes('EAN_8')) add(ZX.BarcodeFormat.EAN_8);
          if (!allow || allow.includes('UPC_A')) add(ZX.BarcodeFormat.UPC_A);
          if (!allow || allow.includes('UPC_E')) add(ZX.BarcodeFormat.UPC_E);
          if (!allow || allow.includes('CODE_128')) add(ZX.BarcodeFormat.CODE_128);
          if (!allow || allow.includes('CODE_39')) add(ZX.BarcodeFormat.CODE_39);
          if (!allow || allow.includes('ITF')) add(ZX.BarcodeFormat.ITF);
          if (!allow || allow.includes('CODABAR')) add(ZX.BarcodeFormat.CODABAR);
          if (!allow || allow.includes('QR_CODE')) add(ZX.BarcodeFormat.QR_CODE);
          if (!allow || allow.includes('DATA_MATRIX')) add(ZX.BarcodeFormat.DATA_MATRIX);
          if (!allow || allow.includes('AZTEC')) add(ZX.BarcodeFormat.AZTEC);
          if (!allow || allow.includes('PDF_417')) add(ZX.BarcodeFormat.PDF_417);

          if (!zxingFormats.length) {
            zxingFormats.push(ZX.BarcodeFormat.EAN_13, ZX.BarcodeFormat.CODE_128);
          }
          hints.set(ZX.DecodeHintType.POSSIBLE_FORMATS, zxingFormats);
        // SPEED: these hints can be expensive; keep them off unless explicitly enabled.
        if (state.zxingTryHarder === true) {
          try {
            hints.set(ZX.DecodeHintType.TRY_HARDER, true);
          } catch (_) {}
        }
        if (state.zxingAlsoInverted === true) {
          try {
            if (ZX.DecodeHintType.ALSO_INVERTED) {
              hints.set(ZX.DecodeHintType.ALSO_INVERTED, true);
            }
          } catch (_) {}
        }
        reader = new ReaderCtor(hints);
      } else {
        reader = new ReaderCtor();
      }
    } catch (_) {
      reader = new ReaderCtor();
    }
    state.zxing.reader = reader;
    state.zxing.video = video;

    // Note: decodeFromVideoDevice runs until reader.reset() is called.
    // It throws NotFoundException frequently when nothing is in view; ignore those.
      // Note: ZXing continuous decoding runs until reader.reset() is called.
      // It throws NotFoundException frequently when nothing is in view; ignore those.
    try {
      const handler = (result, err) => {
          if (!state.running || !state.zxing.enabled) return;

          const text = result?.getText ? result.getText() : result?.text;
          if (text && !shouldCooldown(text)) {
            setStatus('Code détecté: ' + String(text));
            state.onDetected(text);
            return;
          }

          // Throttle error surfacing to avoid spamming the UI.
          if (err) {
            const name = err.name || '';
            const msg = String(err.message || '');
            const isNotFound =
              name === 'NotFoundException' ||
              name === 'NotFoundError' ||
              /notfound/i.test(name) ||
              msg.includes('No MultiFormat Readers were able to detect the code');
            if (!isNotFound) {
              const t = nowMs();
              if (!state.zxing.lastErrAt || t - state.zxing.lastErrAt > 2500) {
                state.zxing.lastErrAt = t;
                if (state.onError) state.onError(err);
              }
            }
          }
        };

      // Prefer explicit constraints to get a sharper stream on many devices.
      setStatus('Demande accès caméra…');
      const constraintCandidates = [
        {
          video: {
            facingMode: { ideal: 'environment' },
            width: { ideal: 1920 },
            height: { ideal: 1080 },
            frameRate: { ideal: 30, max: 60 },
            advanced: [{ focusMode: 'continuous' }, { exposureMode: 'continuous' }],
          },
          audio: false,
        },
        {
          video: {
            facingMode: { ideal: 'environment' },
            width: { ideal: 1280 },
            height: { ideal: 720 },
            frameRate: { ideal: 30, max: 60 },
            advanced: [{ focusMode: 'continuous' }, { exposureMode: 'continuous' }],
          },
          audio: false,
        },
        {
          video: {
            facingMode: { ideal: 'environment' },
            width: { ideal: 640 },
            height: { ideal: 480 },
          },
          audio: false,
        },
        {
          video: { facingMode: { ideal: 'environment' } },
          audio: false,
        },
        // PC fallback (no facingMode)
        {
          video: true,
          audio: false,
        },
      ];
        const canDecodeFromVideoElement = reader && typeof reader.decodeFromVideoElementContinuously === 'function';
        const canDecodeFromStream = reader && typeof reader.decodeFromStream === 'function';
        const canDecodeFromVideoDevice = reader && typeof reader.decodeFromVideoDevice === 'function';

      const pickPreferredVideoDeviceId = async () => {
        try {
          if (!navigator.mediaDevices || typeof navigator.mediaDevices.enumerateDevices !== 'function') return null;
          const devices = await navigator.mediaDevices.enumerateDevices();
          const cams = (devices || []).filter((d) => d && d.kind === 'videoinput');
          if (cams.length === 0) return null;

          // Prefer back/environment when labels are available
          const withLabels = cams.filter((c) => typeof c.label === 'string' && c.label.trim() !== '');
          const preferred = (withLabels.length ? withLabels : cams).find((c) => {
            const label = String(c.label || '').toLowerCase();
            return label.includes('back') || label.includes('rear') || label.includes('environment');
          });
          return (preferred || cams[cams.length - 1]).deviceId || null;
        } catch (_) {
          return null;
        }
      };

      const getStreamWithFallback = async () => {
        let lastErr;
        const preferredDeviceId = await pickPreferredVideoDeviceId();
        for (const c of constraintCandidates) {
          try {
            // If we have a preferred deviceId (often helps on PC), try to force it
            if (preferredDeviceId && c && typeof c === 'object' && c.video && typeof c.video === 'object') {
              const forced = {
                ...c,
                video: {
                  ...c.video,
                  deviceId: { exact: preferredDeviceId },
                },
              };
              return await navigator.mediaDevices.getUserMedia(forced);
            }
            return await navigator.mediaDevices.getUserMedia(c);
          } catch (e) {
            lastErr = e;
          }
        }
        throw lastErr || new Error('Impossible d\'accéder à la caméra.');
      };

      // Strategy:
      // 1) If decodeFromVideoElementContinuously exists, we manage getUserMedia and give ZXing the video.
      // 2) Else if decodeFromStream exists, same idea.
      // 3) Else fall back to decodeFromVideoDevice (ZXing manages getUserMedia). Avoid requesting the camera twice.

      if (!canDecodeFromVideoElement && !canDecodeFromStream && canDecodeFromVideoDevice) {
        // Let ZXing open the camera (more compatible for some builds).
        pickPreferredVideoDeviceId()
          .then((preferredDeviceId) => {
            setStatus('Caméra active (ZXing) — scanne…');
            const p = reader.decodeFromVideoDevice(preferredDeviceId || null, video, handler);
            if (p && typeof p.catch === 'function') {
              p.catch((e) => {
                if (!state.running || !state.zxing.enabled) return;
                state.running = false;
                state.zxing.enabled = false;
                if (state.onError) state.onError(e);
              });
            }
          })
          .catch((e) => {
            if (!state.running || !state.zxing.enabled) return;
            state.running = false;
            state.zxing.enabled = false;
            if (state.onError) state.onError(e);
          });
        return;
      }

      // Manage getUserMedia ourselves (better control on focus/zoom/resolution).
      getStreamWithFallback()
        .then(async (stream) => {
          if (!state.running || !state.zxing.enabled) {
            try { stream.getTracks().forEach((t) => t.stop()); } catch (_) {}
            return;
          }

          video.srcObject = stream;

          // Try to request better focus/zoom when supported (best-effort).
          try {
            const track = stream.getVideoTracks && stream.getVideoTracks()[0];
            if (track && typeof track.getCapabilities === 'function' && typeof track.applyConstraints === 'function') {
              const caps = track.getCapabilities();
              if (caps && Array.isArray(caps.focusMode) && caps.focusMode.includes('continuous')) {
                await track.applyConstraints({ advanced: [{ focusMode: 'continuous' }] });
              }
              if (caps && Array.isArray(caps.exposureMode) && caps.exposureMode.includes('continuous')) {
                await track.applyConstraints({ advanced: [{ exposureMode: 'continuous' }] });
              }
              if (caps && caps.zoom && typeof caps.zoom.max === 'number') {
                const z = Math.min(caps.zoom.max, 3);
                if (z > 1) await track.applyConstraints({ advanced: [{ zoom: z }] });
              }
            }
          } catch (_) {}

          try { await video.play(); } catch (_) {}

          try {
            const w = video.videoWidth || 0;
            const h = video.videoHeight || 0;
            if (w > 0 && h > 0) setStatus('Caméra active (ZXing ' + w + 'x' + h + ') — scanne…');
            else setStatus('Caméra active (ZXing) — scanne…');
          } catch (_) {
            setStatus('Caméra active (ZXing) — scanne…');
          }

          let p;
          if (canDecodeFromVideoElement) {
            // Most compatible with @zxing/library builds when video already has a stream.
            reader.decodeFromVideoElementContinuously(video, handler);
          } else if (canDecodeFromStream) {
            p = reader.decodeFromStream(stream, video, handler);
          } else if (canDecodeFromVideoDevice) {
            // Last resort (but we already have a stream) — keep behavior for odd builds.
            p = reader.decodeFromVideoDevice(null, video, handler);
          }

          if (p && typeof p.catch === 'function') {
            p.catch((e) => {
              if (!state.running || !state.zxing.enabled) return;
              state.running = false;
              state.zxing.enabled = false;
              if (state.onError) state.onError(e);
            });
          }
        })
        .catch((e) => {
          if (!state.running || !state.zxing.enabled) return;
          state.running = false;
          state.zxing.enabled = false;
          if (state.onError) state.onError(e);
        });

      // Watchdog: if ZXing is running but doesn't detect anything, fallback to Quagga.
      // This helps on some webcams/phones where ZXing struggles with 1D barcodes.
      try {
        if (state.allowQuagga && typeof Quagga !== 'undefined') {
          if (state.zxing.fallbackTimer) {
            clearTimeout(state.zxing.fallbackTimer);
            state.zxing.fallbackTimer = 0;
          }
          state.zxing.fallbackTimer = setTimeout(() => {
            if (!state.running || !state.zxing.enabled) return;
            if (state.zxing.fallbackTried) return;

            const t = nowMs();
            const last = state.lastDetectedAt || 0;
            if (last && t - last < 6000) return; // recently detected, keep ZXing

            state.zxing.fallbackTried = true;
            setStatus('ZXing ne détecte pas — bascule sur Quagga…');
            try { stop(); } catch (_) {}
            // Re-start on same target
            const targetEl = document.getElementById(state.targetId);
            if (targetEl) startQuagga(targetEl);
          }, 9000);
        }
      } catch (_) {}
    } catch (e) {
      state.running = false;
      state.zxing.enabled = false;
      throw e;
    }
  }

  function start(targetId) {
    state.targetId = targetId;

    if (!window.isSecureContext && window.location.hostname !== 'localhost' && window.location.hostname !== '127.0.0.1') {
      const err = new Error('Caméra indisponible: ouvrez le site en HTTPS ou sur localhost.');
      if (state.onError) state.onError(err);
      return;
    }

    const target = document.getElementById(targetId);
    if (!target) {
      const err = new Error('Scanner target not found: ' + targetId);
      if (state.onError) state.onError(err);
      return;
    }

    const rect = target.getBoundingClientRect();
    if (!rect || rect.width < 40 || rect.height < 40) {
      const err = new Error('Zone scanner trop petite ou non visible. Réessaye après le rendu de la page.');
      if (state.onError) state.onError(err);
      return;
    }

    if (state.running) {
      return;
    }

    setStatus('Initialisation scanner…');

    // Prefer native detector when available (more stable on mobile)
    if (canUseNativeDetector()) {
      startNative(target).catch((e) => {
        state.running = false;
        state.native.enabled = false;
        if (state.onError) state.onError(e);
      });
      return;
    }

    // Next: ZXing (very stable fallback when BarcodeDetector isn't available)
    if (canUseZXing()) {
      try {
        startZXing(target);
      } catch (e) {
        state.running = false;
        state.zxing.enabled = false;
        if (state.onError) state.onError(e);
      }
      return;
    }

    // Last resort: Quagga (optional; can crash on some devices)
    if (!state.allowQuagga) {
      const err = new Error(
        'Caméra: ce navigateur ne supporte pas BarcodeDetector/ZXing. Utilise Chrome ou active le fallback Quagga.'
      );
      if (state.onError) state.onError(err);
      return;
    }
    startQuagga(target);
  }

  function init(targetId) {
    // Backward-compatible alias
    start(targetId);
  }

  function stop() {
    try {
      state.running = false;

      // Clear ZXing fallback watchdog
      if (state.zxing.fallbackTimer) {
        clearTimeout(state.zxing.fallbackTimer);
        state.zxing.fallbackTimer = 0;
      }

      // Stop native mode
      if (state.native.enabled) {
        state.native.scanning = false;
        if (state.native.rafId) {
          cancelAnimationFrame(state.native.rafId);
          state.native.rafId = 0;
        }
        if (state.native.stream) {
          state.native.stream.getTracks().forEach((t) => t.stop());
        }
        if (state.native.video) {
          state.native.video.srcObject = null;
        }

        state.native.enabled = false;
        state.native.detector = null;
        state.native.stream = null;
        state.native.video = null;
        state.native.canvas = null;
      }

      // Stop ZXing mode
      if (state.zxing.enabled) {
        try {
          if (state.zxing.reader && typeof state.zxing.reader.reset === 'function') {
            state.zxing.reader.reset();
          }
        } catch (_) {}
        try {
          const stream = state.zxing.video?.srcObject;
          if (stream && typeof stream.getTracks === 'function') {
            stream.getTracks().forEach((t) => t.stop());
          }
        } catch (_) {}
        try {
          if (state.zxing.video) state.zxing.video.srcObject = null;
        } catch (_) {}

        state.zxing.enabled = false;
        state.zxing.reader = null;
        state.zxing.video = null;
        state.zxing.fallbackTried = false;
      }

      // Stop Quagga mode
      if (typeof Quagga !== 'undefined') {
        Quagga.stop();
      }
    } catch (_) {
      // ignore
    }
  }

  return { init, start, stop };
}
