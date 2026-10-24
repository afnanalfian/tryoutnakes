/**
 * Render LaTeX dengan KaTeX (dimuat malas dari CDN hanya jika ada rumus).
 * Delimiter: \( ... \) inline, \[ ... \] dan $$ ... $$ display.
 */
const cfg = () => window.APP_MATH || {};
let loading = null;

function loadCss(href) {
    if (document.querySelector(`link[href="${href}"]`)) return;
    const l = document.createElement('link');
    l.rel = 'stylesheet';
    l.href = href;
    document.head.appendChild(l);
}

function loadScript(src) {
    return new Promise((resolve, reject) => {
        if (document.querySelector(`script[src="${src}"]`)) return resolve();
        const s = document.createElement('script');
        s.src = src;
        s.defer = true;
        s.onload = resolve;
        s.onerror = reject;
        document.head.appendChild(s);
    });
}

export function loadKatex() {
    if (window.renderMathInElement) return Promise.resolve();
    if (!loading) {
        loadCss(cfg().katexCss);
        loading = loadScript(cfg().katexJs).then(() => loadScript(cfg().autoRenderJs));
    }
    return loading;
}

const hasMath = (text) => /\\\(|\\\[|\$\$/.test(text || '');

export function renderMath(root = document.body) {
    if (!root) return;
    const targets = root.matches?.('[data-math]') ? [root] : [...root.querySelectorAll('[data-math]')];
    const pending = targets.filter((el) => hasMath(el.textContent));
    if (!pending.length) return;

    loadKatex().then(() => {
        pending.forEach((el) => {
            try {
                window.renderMathInElement(el, {
                    delimiters: [
                        { left: '$$', right: '$$', display: true },
                        { left: '\\[', right: '\\]', display: true },
                        { left: '\\(', right: '\\)', display: false },
                    ],
                    throwOnError: false,
                    strict: 'ignore',
                });
            } catch (e) {
                console.warn('KaTeX', e);
            }
        });
    });
}
