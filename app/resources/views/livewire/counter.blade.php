<div class="flex flex-col items-center gap-4 p-6">
    <div class="text-5xl font-bold tabular-nums text-foreground">{{ $count }}</div>
    <div class="flex items-center gap-2">
        <button
            wire:click="decrement"
            class="inline-flex items-center justify-center h-10 px-4 rounded-md bg-secondary text-secondary-foreground hover:bg-secondary/80 transition-colors text-sm font-medium"
        >
            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 mr-1" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/></svg>
            Decrement
        </button>
        <button
            wire:click="increment"
            class="inline-flex items-center justify-center h-10 px-4 rounded-md bg-primary text-primary-foreground hover:bg-primary/90 transition-colors text-sm font-medium"
        >
            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 mr-1" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="M12 5v14"/></svg>
            Increment
        </button>
    </div>
    <p class="text-xs text-muted-foreground">This is a Livewire component making server requests</p>
</div>
