<template>
    <div class="row page-photos">
        <div v-for="(post, index) in posts" :key="index" class="div-img col-3">
            <div v-on:click='showImage(post.path)' v-bind:style="{backgroundImage: `url('` + post.path + `')`}"></div>
        </div>
        <div class="text-center text-uppercase p-5" v-if="posts.length == 0">
            <i class="far fa-grin-squint-tears mb-2" style="font-size:50px; color:#ff7e67"></i>
            <p style="font-size:13px; color:#707070; letter-spacing: 1px;">please visit next time. empty photos !</p>
        </div>
        <div class="modal box-show-images" ref="modal">
            <i class="fa fa-times sticky-top position-fixed" v-on:click="closeModal()"></i>
            <div class="modal-dialog modal-dialog-centered">
                <img :src="image">
            </div>
        </div>
    </div>
</template>
<script>
    export default {
        data() {
            return {
                user:this.$store.getters.getUser,
                posts: {},
                image: '',
            }
        },
        mounted() {
            axios
            .get('/api/user/'+this.$route.params.username)
            .then(response => {
                const items = response.data.posts.filter(post => post.path != '');
                this.posts = items
            })
            .catch(error => {
                this.$router.back()
            });
        },
        methods: {
            showImage(base64) {
                $(this.$refs.modal).modal('show')
                this.image = base64
            },
            closeModal() {
                $(this.$refs.modal).modal('toggle')
                this.image = ''
            }
        }
    }
</script>