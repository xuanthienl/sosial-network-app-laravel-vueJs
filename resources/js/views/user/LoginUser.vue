<template>
    <div class="box-content p-5 shadow text-center form-login">
        <form @submit.prevent="login" v-if="!forgotPassword">
            <h3 >Login</h3>
            <div class="d-flex align-items-center rounded-pill box-input">
                <i class="fas fa-envelope-open"></i>
                <input type="email" placeholder="your email." v-model="email">
            </div>
            <div class="d-flex align-items-center rounded-pill box-input">
                <i class="fas fa-key"></i>
                <input type="password" placeholder="your password." v-model="password">
            </div>

            <div class="d-flex justify-content-between">
                <label class="text-left box-checkbox pb-3">
                    <p>Remember Me</p>
                    <input type="checkbox" v-model="remember_me">
                    <span class="checkmark"></span>
                </label>
                <p class="forgot-pw" v-on:click="forgotPassword = true">forgot password.</p>
            </div>

            <p v-if="message != ''" class="text-message rounded-pill">{{message}}</p>
            <button type="submit" class="w-100 btn rounded-pill">Login</button>
            <div>
                <router-link :to="{name: 'register'}">or... Create new account</router-link>
            </div>
        </form>

        <form @submit.prevent="resetPassword" v-else>
            <h3>Forgot Password</h3>
            <div class="d-flex align-items-center rounded-pill box-input">
                <i class="fas fa-envelope-open"></i>
                <input type="email" placeholder="your email." v-model="email">
            </div>
            <p v-if="message != ''" class="text-message rounded-pill">{{message}}</p>
            <button type="submit" class="w-100 btn rounded-pill">Send password reset email</button>
        </form>
    </div>
</template>

<script>
    export default {
        data() {
            return {
                email:'',
                password:'',
                remember_me:'',
                message: '',
                forgotPassword: false,
            }
        },
        methods: {
            login() {
                this.$store.dispatch('retrieveToken', {
                    email: this.email,
                    password: this.password,
                })
                .then(response => {
                    this.$router.push({ name: 'home' }).catch(()=>{});
                })
                .catch((error) => {
                    if (error.response) {
                        if(error.response.data.message) {
                            this.message = error.response.data.message
                        } else {
                            this.message = ''
                        }
                    } else {
                        return
                    }
                });
            },
            resetPassword() {
                axios.post('/api/reset-pasword', {
                    email: this.email,
                })
                .then((response) => {
                    this.forgotPassword = false
                    this.message = ''
                })
                .catch((error) => {
                    if (error.response) {
                        if(error.response.data.message) {
                            this.message = error.response.data.message
                        } else {
                            this.message = ''
                        }
                    } else {
                        return
                    }
                });
            },
        }
    }
</script>