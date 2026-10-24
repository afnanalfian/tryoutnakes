<div class="border-b border-amber-200 bg-gradient-to-r from-amber-50 via-amber-50 to-orange-50">
    <div class="mx-auto flex max-w-7xl flex-col gap-2 px-4 py-2.5 sm:flex-row sm:items-center sm:justify-between sm:px-6 lg:px-8">
        <p class="flex items-center gap-2 text-sm font-medium text-amber-900">
            <span class="relative flex h-2.5 w-2.5"><span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-amber-500 opacity-70"></span><span class="relative inline-flex h-2.5 w-2.5 rounded-full bg-amber-500"></span></span>
            Tryout <strong class="font-bold">{{ $activeAttempt->tryout->title }}</strong> sedang berlangsung — sisa {{ (int) ceil($activeAttempt->remainingSeconds() / 60) }} menit.
        </p>
        <a href="{{ route('exam.show', $activeAttempt) }}" class="btn btn-sm self-start bg-amber-500 text-white hover:bg-amber-600 sm:self-auto">Lanjutkan Ujian <x-icon name="arrow-right" class="h-3.5 w-3.5" /></a>
    </div>
</div>
