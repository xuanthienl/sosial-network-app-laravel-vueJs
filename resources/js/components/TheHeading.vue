<template>
    <div class="sticky-top">
        <div class="d-none d-lg-block heading">
            <div class="box-item rounded-pill shadow-sm" v-on:click="myHome()" v-bind:class="[$route.name == 'home' ? 'active' : '']">
                <div class="hexagon-wrapper">
                    <div class="hexagon">
                        <i class="fas fa-home"></i>
                    </div>
                </div>
                <span>Home</span>
            </div>
            <router-link :to="{name:'home', query: { action: 'create' }}">
                <div class="box-item rounded-pill shadow-sm">
                    <div class="hexagon-wrapper">
                        <div class="hexagon">
                            <i class="fas fa-feather-alt"></i>
                        </div>
                    </div>
                    <span>Post</span>
                </div>
            </router-link>
            <router-link :to="{name:'home', query: { action: 'create' }}">
                <div class="box-item rounded-pill shadow-sm">
                    <div class="hexagon-wrapper">
                        <div class="hexagon">
                            <i class="fas fa-bell"></i>
                        </div>
                    </div>
                    <span>Notifications</span>
                </div>
            </router-link>
            <router-link :to="{name: 'messages'}">
                <div class="box-item rounded-pill shadow-sm" v-bind:class="[$route.name == 'messages' ? 'active' : '']">
                    <div class="hexagon-wrapper">
                        <div class="hexagon">
                            <i class="fas fa-envelope-open"></i>
                        </div>
                    </div>
                    <span>Messages</span>
                </div>
            </router-link>
            <router-link :to="{name: 'profile', params: { username: getUser.username } }">
                <div class="box-item rounded-pill shadow-sm" v-bind:class="[$route.name == 'profile' ? 'active' : '']">
                    <div class="hexagon-wrapper">
                        <div class="hexagon">
                            <i class="fas fa-user"></i>
                        </div>
                    </div>
                    <span>Profile</span>
                </div>
            </router-link>
            <router-link :to="{name: 'music'}">
                <div class="box-item rounded-pill shadow-sm">
                    <div class="hexagon-wrapper">
                        <div class="hexagon">
                            <i class="fas fa-compact-disc"></i>
                        </div>
                    </div>
                    <span>Chill</span>
                </div>
            </router-link>
            <router-link :to="{name: 'logout'}">
                <div class="box-item rounded-pill shadow-sm sidebar-footer">
                    <div class="hexagon-wrapper">
                        <div class="hexagon">
                            <i class="fas fa-power-off"></i>
                        </div>
                    </div>
                    <span>Logout</span>
                </div>
            </router-link>
        </div>

        <div class="d-block d-lg-none sticky-top position-fixed w-100 d-flex justify-content-between shadow-sm heading-sp p-3">
            <router-link :to="{name:'home', query: { action: 'scroll' }}">
                <div class="hexagon-wrapper">
                    <div class="hexagon" v-on:click="myHome()" v-bind:class="[$route.name == 'home' ? 'box-active' : '']">
                        <i class="fas fa-home"></i>
                    </div>
                </div>
            </router-link>
            <router-link :to="{name:'home', query: { action: 'create' }}">
                <div class="hexagon-wrapper">
                    <div class="hexagon">
                        <i class="fas fa-feather-alt"></i>
                    </div>
                </div>
            </router-link>
            <router-link :to="{name: 'messages'}">
                <div class="hexagon-wrapper">
                    <div class="hexagon">
                        <i class="fas fa-bell"></i>
                    </div>
                </div>
            </router-link>
            <router-link :to="{name: 'messages'}">
                <div class="hexagon-wrapper">
                    <div class="hexagon" v-bind:class="[$route.name == 'messages' ? 'box-active' : '']">
                        <i class="fas fa-envelope-open"></i>
                    </div>
                </div>
            </router-link>
            <router-link :to="{name: 'profile', params: { username: getUser.username } }">
                <div class="hexagon-wrapper">
                    <div class="hexagon" v-bind:class="[$route.name == 'profile' ? 'box-active' : '']">
                        <i class="fas fa-user"></i>
                    </div>
                </div>
            </router-link>
            <router-link :to="{name: 'music'}">
                <div class="hexagon-wrapper">
                    <div class="hexagon">
                        <i class="fas fa-compact-disc"></i>
                    </div>
                </div>
            </router-link>
            <router-link :to="{name:'logout'}">
                <div class="hexagon-wrapper">
                    <div class="hexagon">
                        <i class="fas fa-power-off"></i>
                    </div>
                </div>
            </router-link>
        </div>
    </div>
</template>

<script>
    export default {
        data() {
            return {
                user: this.$store.getters.getUser,
            }
        },
        computed: {
            getUser() {
                return this.$store.getters.getUser
            }
        },
        methods: {
            myHome() {
                if(this.$route.name == 'home') {
                    this.$router.push({ name: 'home', query: { action: 'scroll' }});
                } else {
                    this.$router.push({ name: 'home'});
                }
            }
        }
    }
</script>
