<template>
    <v-app>
    <div class="container-fluid container-xl vh-100 d-flex overflow-scroll p-3">
        <my-heading v-if="loggedIn"></my-heading>
        <div class="mx-auto align-self-center" v-if="$route.name == 'login' || $route.name == 'register'">
            <router-view></router-view>
        </div>
        <div class="w-100 px-5 px-lg-10 mt-15 pt-5 mt-lg-0 pt-lg-0" v-else>
            <router-view class="position-relative"></router-view>
        </div>
        <my-sidebar v-if="loggedIn && $route.name != 'messages'"></my-sidebar>
        <notifications class="notifications" group="foo"/>
    </div>
    </v-app>
</template>
<script>
    export default {
        data() {
            return {
                user:this.$store.getters.getUser,
                tracks: this.$store.getters.getTracks == '' ? '' : this.$store.getters.getTracks,
            }
        },
        computed: {
            loggedIn() {
                return this.$store.getters.loggedIn
            },
            getUser() {
                return this.$store.getters.getUser
            }
        }
    }
</script>