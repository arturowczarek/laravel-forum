<template>
    <button :class="clases" @click="subscribe">Subscribe</button>
</template>

<script>
    export default {
        props: ['active'],

        computed: {
            clases() {
                return ['btn', this.active ? 'btn-primary' : 'btn-default'];
            }
        },

        methods: {
            subscribe() {
                let requestType = this.active ? 'delete' : 'post';
                axios[requestType](`${location.pathname}/subscriptions`);

                this.active = !this.active;

                flash('Subscribed');
            }
        }
    }
</script>