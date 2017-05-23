<template>
    <div>
        <div v-if="signedIn">
        <textarea name="body"
                  id="body"
                  class="form-control"
                  placeholder="Have something to say?"
                  rows="5"
                  required
                  v-model="body"></textarea>
            <button class="btn btn-default" @click="addReply">Post</button>
        </div>

        <p class="text-center" v-else>
            <a href="/login">Please sign in to participate in this discussion.</a>
        </p>
    </div>
</template>

<script>
    export default {
        props: ['endpoint'],

        data() {
            return {
                body: ''
            };
        },

        computed: {
            signedIn() {
                return window.App.signedIn;
            }
        },

        methods: {
            addReply() {
                axios.post(this.endpoint, { body: this.body })
                    .then(({ data }) => {
                        this.body = '';

                        flash('Your reply has been posted.');
                        this.$emit('created', data);
                    });
            }
        }
    }
</script>