if ('serviceWorker' in navigator) {
    window.addEventListener('load', () => {
        navigator.serviceWorker.register('/sw.js').catch(() => {});
    });
}

document.addEventListener('livewire:init', () => {
    Livewire.on('open-url', ({ url }) => window.open(url, '_blank'));
});

