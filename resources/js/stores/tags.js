document.addEventListener('livewire:init', () => {
    Alpine.store('tags', {
        list: [],
        isLoaded: false,

        init() {
            if (!this.isLoaded) {
                this.loadFromLivewire();
            }
        },

        loadFromLivewire() {
            this.isLoaded = true;
        },

        setTags(tags) {
            this.list = tags;
            this.isLoaded = true;
        },

        invalidate() {
            this.isLoaded = false;
            this.list = [];
        },

        addTag(tag) {
            if (!this.list.find(t => t.id === tag.id)) {
                this.list.push(tag);
            }
        }
    });
});
