/**
 * Mesin ujian (Alpine). Navigasi instan di sisi klien, jawaban disimpan
 * ke server via antrean dengan retry + cadangan localStorage (tahan sinyal putus).
 */
import { renderMath } from './math';

export default (config) => ({
    questions: config.questions,          // [{id, no, subtest, answer, flagged}]
    current: 0,
    remaining: config.remaining,
    deadline: Date.now() + config.remaining * 1000,
    queue: [],
    saving: false,
    online: navigator.onLine,
    lastSavedAt: null,
    saveError: false,
    navOpen: false,
    confirmOpen: false,
    submitting: false,
    fontScale: 1,
    warned5: false,
    warned1: false,
    toast: null,
    storeKey: `exam:${config.attemptId}`,

    init() {
        this.restoreLocal();
        this.tick();
        this.timer = setInterval(() => this.tick(), 1000);
        this.syncTimer = setInterval(() => this.syncTime(), 60000);
        try { this.fontScale = Number(localStorage.getItem('exam:font') || 1); } catch (e) {}

        window.addEventListener('online', () => { this.online = true; this.flush(); });
        window.addEventListener('offline', () => { this.online = false; });
        window.addEventListener('beforeunload', (e) => {
            if (this.queue.length && !this.submitting) { e.preventDefault(); e.returnValue = ''; }
        });
        document.addEventListener('visibilitychange', () => { if (!document.hidden) this.syncTime(); });

        const start = this.questions.findIndex((q) => !q.answer);
        this.current = start > -1 ? start : 0;
        this.$nextTick(() => renderMath(this.$root));
    },

    /* ---------- Turunan ---------- */
    get q() { return this.questions[this.current]; },
    get answeredCount() { return this.questions.filter((q) => q.answer).length; },
    get flaggedCount() { return this.questions.filter((q) => q.flagged).length; },
    get emptyCount() { return this.questions.length - this.answeredCount; },
    get progress() { return Math.round((this.answeredCount / this.questions.length) * 100); },
    get clock() {
        const s = Math.max(0, this.remaining);
        const h = String(Math.floor(s / 3600)).padStart(2, '0');
        const m = String(Math.floor((s % 3600) / 60)).padStart(2, '0');
        const d = String(s % 60).padStart(2, '0');
        return `${h}:${m}:${d}`;
    },
    get danger() { return this.remaining <= 300; },
    get saveLabel() {
        if (!this.online) return 'Offline — jawaban aman di perangkat';
        if (this.saving || this.queue.length) return 'Menyimpan…';
        if (this.saveError) return 'Gagal menyimpan, mencoba lagi…';
        return this.lastSavedAt ? 'Semua jawaban tersimpan' : 'Siap';
    },
    sectionStart(sub) { return this.questions.findIndex((q) => q.subtest === sub); },
    sectionStats(sub) {
        const list = this.questions.filter((q) => q.subtest === sub);
        return `${list.filter((q) => q.answer).length}/${list.length}`;
    },
    cellClass(i) {
        const q = this.questions[i];
        return {
            'ring-2 ring-brand-500 ring-offset-2': i === this.current,
            'bg-amber-400 text-amber-950 border-amber-400': q.flagged,
            'bg-navy-900 text-white border-navy-900': q.answer && !q.flagged,
            'bg-white text-slate-600 border-slate-200 hover:border-navy-300': !q.answer && !q.flagged,
        };
    },

    /* ---------- Navigasi ---------- */
    go(i) {
        if (i < 0 || i >= this.questions.length) return;
        this.current = i;
        this.navOpen = false;
        this.$nextTick(() => {
            this.$refs.stage?.scrollTo?.({ top: 0, behavior: 'smooth' });
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });
    },
    next() { this.go(this.current + 1); },
    prev() { this.go(this.current - 1); },
    key(e) {
        if (this.confirmOpen || ['INPUT', 'TEXTAREA'].includes(e.target.tagName)) return;
        if (e.key === 'ArrowRight') this.next();
        else if (e.key === 'ArrowLeft') this.prev();
        else if (/^[a-eA-E]$/.test(e.key)) {
            const opt = this.$root.querySelector(`[data-q="${this.q.id}"] [data-label="${e.key.toUpperCase()}"]`);
            opt?.click();
        } else if (e.key.toLowerCase() === 'r') this.toggleFlag();
    },

    /* ---------- Jawaban ---------- */
    choose(optionId) {
        this.q.answer = optionId;
        this.enqueue(this.q);
    },
    clearAnswer() {
        this.q.answer = null;
        this.enqueue(this.q);
    },
    toggleFlag() {
        this.q.flagged = !this.q.flagged;
        this.enqueue(this.q);
    },
    enqueue(q) {
        this.queue = this.queue.filter((x) => x.question_id !== q.id);
        this.queue.push({ question_id: q.id, option_id: q.answer, flagged: q.flagged });
        this.persistLocal();
        this.flush();
    },
    async flush() {
        if (this.saving || !this.queue.length || !this.online) return;
        this.saving = true;
        const item = this.queue[0];
        try {
            const res = await this.post(config.answerUrl, item);
            if (res.status === 409 || res.status === 410) { return this.finishedElsewhere(); }
            if (res.status === 419) { this.notify('Sesi berakhir, memuat ulang…'); return setTimeout(() => location.reload(), 1200); }
            if (!res.ok && res.status !== 422) throw new Error(res.status);
            this.queue = this.queue.filter((x) => x !== item);
            this.saveError = false;
            this.lastSavedAt = new Date();
            this.persistLocal();
        } catch (e) {
            this.saveError = true;
            await new Promise((r) => setTimeout(r, 2500));
        } finally {
            this.saving = false;
        }
        if (this.queue.length) this.flush();
    },
    post(url, body) {
        return fetch(url, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', Accept: 'application/json', 'X-CSRF-TOKEN': config.csrf, 'X-Requested-With': 'XMLHttpRequest' },
            body: JSON.stringify(body || {}),
            credentials: 'same-origin',
        });
    },
    persistLocal() {
        try { localStorage.setItem(this.storeKey, JSON.stringify(this.queue)); } catch (e) {}
    },
    restoreLocal() {
        try {
            const pending = JSON.parse(localStorage.getItem(this.storeKey) || '[]');
            pending.forEach((p) => {
                const q = this.questions.find((x) => x.id === p.question_id);
                if (q) { q.answer = p.option_id; q.flagged = p.flagged; }
            });
            this.queue = pending;
            if (pending.length) this.flush();
        } catch (e) {}
    },

    /* ---------- Waktu ---------- */
    tick() {
        this.remaining = Math.max(0, Math.round((this.deadline - Date.now()) / 1000));
        if (!this.warned5 && this.remaining <= 300 && this.remaining > 60) { this.warned5 = true; this.notify('⏰ Sisa waktu 5 menit. Periksa jawaban yang masih ragu-ragu.'); }
        if (!this.warned1 && this.remaining <= 60 && this.remaining > 0) { this.warned1 = true; this.notify('⚠️ Sisa waktu 1 menit!'); }
        if (this.remaining <= 0 && !this.submitting) this.submit(true);
    },
    async syncTime() {
        try {
            const res = await fetch(config.syncUrl, { headers: { Accept: 'application/json' }, credentials: 'same-origin' });
            if (!res.ok) return;
            const data = await res.json();
            if (data.submitted) return this.finishedElsewhere();
            this.deadline = Date.now() + data.remaining * 1000;
        } catch (e) {}
    },

    /* ---------- Selesai ---------- */
    async submit(auto = false) {
        if (this.submitting) return;
        this.submitting = true;
        this.confirmOpen = false;
        if (auto) this.notify('Waktu habis. Jawabanmu dikumpulkan otomatis…');

        const deadlineFlush = Date.now() + 8000;
        while (this.queue.length && Date.now() < deadlineFlush) {
            await this.flush();
            if (this.queue.length) await new Promise((r) => setTimeout(r, 400));
        }
        try {
            const res = await this.post(config.submitUrl, { pending: this.queue });
            const data = await res.json().catch(() => ({}));
            try { localStorage.removeItem(this.storeKey); } catch (e) {}
            window.location.href = data.redirect || config.resultUrl;
        } catch (e) {
            this.submitting = false;
            this.notify('Gagal mengumpulkan. Periksa koneksi lalu coba lagi.');
        }
    },
    finishedElsewhere() {
        this.submitting = true;
        try { localStorage.removeItem(this.storeKey); } catch (e) {}
        window.location.href = config.resultUrl;
    },

    /* ---------- UI ---------- */
    notify(msg) {
        this.toast = msg;
        clearTimeout(this._t);
        this._t = setTimeout(() => (this.toast = null), 4500);
    },
    zoom(delta) {
        this.fontScale = Math.min(1.4, Math.max(0.85, +(this.fontScale + delta).toFixed(2)));
        try { localStorage.setItem('exam:font', this.fontScale); } catch (e) {}
    },
    fullscreen() {
        if (!document.fullscreenElement) document.documentElement.requestFullscreen?.();
        else document.exitFullscreen?.();
    },
});
