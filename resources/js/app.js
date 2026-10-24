import { Livewire, Alpine } from '../../vendor/livewire/livewire/dist/livewire.esm';
import { renderMath } from './math';
import exam from './exam';
import questionEditor from './question-editor';

window.renderMath = renderMath;

Alpine.data('exam', exam);
Alpine.data('questionEditor', questionEditor);

// Sortable dimuat sesuai kebutuhan (hanya di halaman admin susun soal).
Alpine.directive('sortable', (el, { expression }, { evaluate }) => {
    import('sortablejs').then(({ default: Sortable }) => {
        Sortable.create(el, {
            handle: '[data-handle]',
            animation: 150,
            ghostClass: 'opacity-40',
            onEnd: () => {
                const ids = [...el.querySelectorAll('[data-id]')].map((n) => Number(n.dataset.id));
                evaluate(expression)(ids);
            },
        });
    });
});

// Format rupiah sederhana di Alpine
Alpine.magic('rupiah', () => (n) => 'Rp' + new Intl.NumberFormat('id-ID').format(n || 0));

// Render rumus setiap kali konten berubah
const rerender = () => queueMicrotask(() => renderMath(document.body));
document.addEventListener('DOMContentLoaded', rerender);
document.addEventListener('livewire:navigated', rerender);
Livewire.hook('commit', ({ succeed }) => succeed(() => setTimeout(rerender, 0)));

Livewire.start();
