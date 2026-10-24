/**
 * Editor soal (admin): sisipkan rumus via MathQuill (mirip Satya), toolbar format,
 * dan pratinjau langsung dengan KaTeX. Bekerja berdampingan dengan wire:model.
 */
import { renderMath } from './math';

const cfg = () => window.APP_MATH || {};
let mqLoading = null;

function script(src) {
    return new Promise((resolve, reject) => {
        const s = document.createElement('script');
        s.src = src; s.onload = resolve; s.onerror = reject;
        document.head.appendChild(s);
    });
}

function loadMathQuill() {
    if (window.MathQuill) return Promise.resolve();
    if (!mqLoading) {
        const l = document.createElement('link');
        l.rel = 'stylesheet'; l.href = cfg().mathquillCss;
        document.head.appendChild(l);
        mqLoading = (window.jQuery ? Promise.resolve() : script(cfg().jquery)).then(() => script(cfg().mathquillJs));
    }
    return mqLoading;
}

function safePreview(value) {
    const v = value || '';
    if (!v.includes('<')) {
        const div = document.createElement('div');
        div.textContent = v;
        return div.innerHTML.replace(/\n/g, '<br>');
    }
    const doc = new DOMParser().parseFromString(`<div>${v}</div>`, 'text/html');
    doc.querySelectorAll('script,style,iframe,object,embed,form,input,button').forEach((n) => n.remove());
    doc.querySelectorAll('*').forEach((n) => [...n.attributes].forEach((a) => {
        if (a.name.startsWith('on') || (['href', 'src'].includes(a.name) && /^\s*javascript:/i.test(a.value))) n.removeAttribute(a.name);
    }));
    let html = doc.body.firstChild.innerHTML;
    if (!/<(p|br|div|li|table)\b/i.test(html)) html = html.replace(/\n/g, '<br>');
    return html;
}

export default () => ({
    mqOpen: false,
    docsOpen: false,
    mqReady: false,
    target: null,
    selStart: 0,
    selEnd: 0,
    displayMode: false,
    mq: null,

    init() {
        this.$nextTick(() => this.refreshAll());
        document.addEventListener('livewire:navigated', () => this.refreshAll());
    },

    refreshAll() {
        this.$root.querySelectorAll('[data-preview-for]').forEach((el) => this.preview(el.dataset.previewFor));
    },

    preview(id) {
        const input = document.getElementById(id);
        const out = this.$root.querySelector(`[data-preview-for="${id}"]`);
        if (!input || !out) return;
        out.innerHTML = input.value.trim() ? safePreview(input.value) : '<span class="text-slate-400">Pratinjau akan tampil di sini…</span>';
        renderMath(out);
    },

    async openMath(id) {
        this.target = document.getElementById(id);
        if (!this.target) return;
        this.selStart = this.target.selectionStart ?? this.target.value.length;
        this.selEnd = this.target.selectionEnd ?? this.selStart;
        this.mqOpen = true;
        await loadMathQuill();
        this.$nextTick(() => {
            if (!this.mq) {
                const MQ = window.MathQuill.getInterface(2);
                this.mq = MQ.MathField(this.$refs.mqField, {
                    spaceBehavesLikeTab: true,
                    leftRightIntoCmdGoes: 'up',
                    restrictMismatchedBrackets: true,
                    sumStartsWithNEquals: true,
                    supSubsRequireOperand: true,
                    autoCommands: 'pi theta alpha beta gamma delta sqrt nthroot sum int infty',
                    handlers: { enter: () => this.insertMath() },
                });
            }
            this.mqReady = true;
            this.mq.latex('');
            this.mq.focus();
        });
    },

    cmd(c) { this.mq?.cmd(c); this.mq?.focus(); },
    write(latex) { this.mq?.write(latex); this.mq?.focus(); },

    insertMath() {
        if (!this.mq || !this.target) return;
        const latex = this.mq.latex().trim();
        if (latex) {
            const token = this.displayMode ? `\\[${latex}\\]` : `\\(${latex}\\)`;
            this.insertAt(this.target, token, this.selStart, this.selEnd);
        }
        this.closeMath();
    },

    closeMath() {
        this.mqOpen = false;
        this.docsOpen = false;
        this.target?.focus();
    },

    insertAt(el, text, start, end) {
        const before = el.value.slice(0, start);
        const after = el.value.slice(end);
        const pad = before && !/\s$/.test(before) ? ' ' : '';
        el.value = before + pad + text + ' ' + after;
        const pos = (before + pad + text + ' ').length;
        el.setSelectionRange(pos, pos);
        el.dispatchEvent(new Event('input', { bubbles: true }));
        this.preview(el.id);
    },

    wrap(id, open, close = '') {
        const el = document.getElementById(id);
        if (!el) return;
        const s = el.selectionStart, e = el.selectionEnd;
        const selected = el.value.slice(s, e);
        el.value = el.value.slice(0, s) + open + selected + close + el.value.slice(e);
        const pos = s + open.length + selected.length + (selected ? close.length : 0);
        el.focus();
        el.setSelectionRange(selected ? pos : s + open.length, selected ? pos : s + open.length);
        el.dispatchEvent(new Event('input', { bubbles: true }));
        this.preview(id);
    },
});
