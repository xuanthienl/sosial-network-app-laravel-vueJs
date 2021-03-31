<template>
    <div class="box-content p-5 shadow text-center form-login">
        <form @submit.prevent="register">
            <h3>Register</h3>
            <div class="d-flex align-items-center rounded-pill box-input">
                <i class="fas fa-user-circle"></i>
                <input type="text" placeholder="your username." v-model="username">
            </div>
            <div class="d-flex align-items-center rounded-pill box-input">
                <i class="fas fa-envelope-open rounded-pill"></i>
                <input type="email" placeholder="your email." v-model="email">
            </div>
            <div class="d-flex align-items-center rounded-pill box-input">
                <i class="fas fa-key rounded-pill"></i>
                <input type="password" placeholder="your password." v-model="password">
            </div>
            <p v-if="message != ''" class="text-message rounded-pill">{{message}}</p>
            <button type="submit" class="w-100 btn rounded-pill">Register</button>
            <div>
                <router-link :to="{name: 'login'}">or... Login</router-link>
            </div>
        </form>
    </div>
</template>

<script>
    export default {
        data() {
            return {
                username:'',
                email:'',
                password:'',
                message: '',
            }
        },
        methods: {
            register() {
                this.$store.dispatch('register', {
                    username: this.username,
                    email: this.email,
                    password: this.password,
                })
                .then(response => {
                    this.$router.push({ name: 'login' })
                })
                .catch((error) => {
                    if (error.response) {
                        if(error.response.data.message) {
                            this.message = error.response.data.message
                        } else {
                            this.message = ''
                        }
                    } else {
                        console.log(error);
                    }
                });
            }
        }
    }
</script>