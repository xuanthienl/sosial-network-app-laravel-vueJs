<template>
    <div class="row page-intro">
        <div class="intro" v-if="empty">
            <div v-if="info.name != ''" class="d-flex align-items-center">
                <i class="fas fa-signature"></i>
                <p>{{info.name}}</p>
            </div>
            <div v-if="info.birth_date != ''" class="d-flex align-items-center">
                <i class="fas fa-birthday-cake"></i>
                <p>{{info.birth_date}}</p>
            </div>
            <div v-if="info.address != ''" class="d-flex align-items-center">
                <i class="fas fa-map-marker-alt"></i>
                <p>{{info.address}}</p>
            </div>
            <div v-if="info.phone_number != ''" class="d-flex align-items-center">
                <i class="fas fa-phone"></i>
                <p>{{info.phone_number}}</p>
            </div>
            <div v-if="info.gender != 0" class="d-flex align-items-center">
                <i class="fas fa-venus-mars"></i>
                <p>{{info.gender == 1 ? 'male' : 'female' }}</p>
            </div>
        </div>
        <div class="text-center text-uppercase p-5" v-else>
            <i class="far fa-grin-squint-tears mb-2" style="font-size:50px; color:#ff7e67"></i>
            <p>please visit next time. {{ info.username }} have not updated their information !</p>
        </div>
    </div>
</template>
<script>
    export default {
        data() {
            return {
                user:this.$store.getters.getUser,
                info: {
                    email: '',
                    username: '',
                    name: '',
                    gender: '',
                    address: '',
                    description: '',
                    phone_number: '',
                    birth_date: '',
                    image_main: '',
                    image_profile: ''
                },
            }
        },
        mounted() {
            axios
            .get('/api/user/'+this.$route.params.username)
            .then(response => {
                this.info.id = response.data.user.id
                this.info.email = response.data.user.email
                this.info.username = response.data.user.username
                this.info.name = response.data.user.name != null ? response.data.user.name : ''
                this.info.gender = response.data.user.gender
                this.info.address = response.data.user.address != null ? response.data.user.address : ''
                this.info.description = response.data.user.description != null ? response.data.user.description : ''
                this.info.phone_number = response.data.user.phone_number != null ? response.data.user.phone_number : ''
                this.info.birth_date = response.data.user.birth_date != null ? response.data.user.birth_date : ''
                this.info.image_main = response.data.user.image_main != null ? response.data.user.image_main : ''
                this.info.image_profile = response.data.user.image_profile != null ? response.data.user.image_profile : ''
            })
            .catch(error => {
                this.$router.back()
            });
        },
        computed: {
            empty() {
                if(this.info.name == '' && this.info.gender == 0 && this.info.address == '' && this.info.phone_number == '' && this.info.birth_date == '') {
                    return false
                } else {
                    return true
                }
            },
        },
        methods: {
            
        }
    }
</script>