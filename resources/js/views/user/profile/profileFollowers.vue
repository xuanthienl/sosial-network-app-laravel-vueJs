<template>
    <div class="row">
        <div v-for="(item, index) in users" :key="index" class="col-6">
            <div class="d-flex align-items-center justify-content-between box-user p-3">
                <div class="d-flex align-items-center">
                    <div class="div-avatar" v-bind:style="{backgroundImage: item.image_profile != '' ? `url('` + item.image_profile + `')` : `url('/images/user.png')`}"></div>
                    <div class="div-name">{{item.username}}</div>
                </div>
                <i class="fas fa-ellipsis-h"></i>
            </div>
        </div>
        <div class="text-center text-uppercase p-5" v-if="users.length == 0">
            <i class="far fa-sad-tear mb-2" style="font-size:50px; color:#ff7e67"></i>
            <p style="font-size:13px; color:#707070; letter-spacing: 1px;">empty followers !</p>
        </div>
    </div>
</template>
<script>
    export default {
        data() {
            return {
                user:this.$store.getters.getUser,
                users: {},
            }
        },
        mounted() {
            axios
            .get('/api/user/'+this.$route.params.username)
            .then(response => {
                this.users = response.data.followers
            })
            .catch(error => {
                this.$router.back()
            });
        }
    }
</script>