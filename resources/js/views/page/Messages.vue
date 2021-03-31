<template>
    <div class="d-flex h-100 messages">
        <div class="col-4 box-info">
            <div class="rounded-pill p-4 w-100 d-flex align-items-center position-relative search-box">
                <div class="d-flex align-items-center w-100 p-0">
                    <input type="text" placeholder="search for people." v-model="keyword" class="w-100">
                    <div class="item loading-3" v-if="loading">
                        <div class="loading"></div>
                    </div>
                    <i class="fas fa-times" v-else-if="keyword != ''" v-on:click="deleteKeywords()"></i>
                    <button type="button" v-else><i class="fa fa-search" aria-hidden="true"></i></button>
                </div>
            </div>
            <div v-if="contacts.length > 0">
                <div class="d-flex justify-content-center justify-content-sm-start align-items-center div-user-select rounded-pill" v-for="(item, index) in contacts" :key="index" v-on:click="selectMessages(index)" v-bind:class="[item.id == contact.id ? 'active' : '']">
                    <img :src="item.image_profile != null ? `../storage/images/users/`+ item.id + `/image_profile/` + item.image_profile : `/images/user.png`" class="img-fluid mx-2">
                    <div class="d-none d-sm-block align-items-center">
                        <p v-bind:class="[item.unread > 0 ? 'unread' : '']">{{ item.username }} <span v-if="item.unread > 0" class="unread">{{`(` + item.unread + `)`}}</span></p>
                        <p>{{item.name}} <span v-if="item.last_message"><i class="fas fa-history mr-1"></i>{{dateFormat(item.last_message)}}</span></p>
                    </div>
                </div>
            </div>
            <div v-else>
                <div class="d-flex align-items-center contacts-empty pb-2">
                    <div class="circle-img mr-2"></div>
                    <div class="p-0">
                        <div class="rounded-pill icon-user"></div>
                        <div class="rounded-pill icon-info"></div>
                    </div>
                </div>
                <div class="d-flex align-items-center contacts-empty pb-2">
                    <div class="circle-img mr-2"></div>
                    <div class="p-0">
                        <div class="rounded-pill icon-user"></div>
                        <div class="rounded-pill icon-info"></div>
                    </div>
                </div>
                <div class="d-flex align-items-center contacts-empty pb-2">
                    <div class="circle-img mr-2"></div>
                    <div class="p-0">
                        <div class="rounded-pill icon-user"></div>
                        <div class="rounded-pill icon-info"></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col mx-4 box-chat">
            <div class="d-flex align-items-center info-contact p-2" v-if="contact.username">
                <img :src="contact.image_profile != null ? `../storage/images/users/`+ contact.id + `/image_profile/` + contact.image_profile : `/images/user.png`" class="img-fluid">
                <div class="d-none d-sm-block align-items-center">
                    <router-link :to="{name: 'profile', params: { username: contact.username } }"><p class="m-0 px-3">{{ contact.username }}</p></router-link>
                    <router-link :to="{name: 'profile', params: { username: contact.username } }"><p class="m-0 px-3">{{contact.name}}</p></router-link>
                </div>
            </div>
            <div class="overflow-scroll px-4 message" ref="feed" v-if="contact.username">
                <div class="p-3 my-2" v-for="(message, index) in messages" :key="index" v-bind:class="[message.from == user.id ? 'your' : 'other']">
                    {{message.text}}
                </div>
            </div>
            <div class="box-input" v-if="contact.username">
                <input class="row p-3 mb-3 rounded-pill" v-model="message" @keydown.enter="send" placeholder="Message...">
            </div>
            <div class="h-100 text-center d-flex align-items-center" v-else>
                <i class="fas fa-envelope-open mx-auto icon-message"></i>
            </div>
        </div>
    </div>
</template>

<script>
    import moment from 'moment';
    export default {
        data() {
            return {
                user: this.$store.getters.getUser,
                token: this.$store.getters.token,
                messages: [],
                contacts: [],
                message: '',
                contact: {},
                keyword: '',
                loading: false,
                users: []
            };
        },
        mounted() {
            Echo.private(`messages`)
                .listen('sendNewNessage', (e) => {
                    this.isSendRealtime(e.message);
                });
    
            this.axios
            .get('/api/contacts', {
                'headers' : {
                    'Accept' : 'application/json',
                    'Authorization' : 'Bearer ' + this.token,
                } 
            })
            .then((response) => {
                if(response.data.empty) {
                    return
                } else {
                    this.contacts = response.data;
                    this.users = response.data;
                    this.contact = this.contacts.slice(0,1);
                    this.selectMessages(0) 
                }
            });
        },
        watch: {
            messages(messages) {
                this.scrollToBottom();
            },
            keyword: function (newKeyword, oldKeyword) {
                this.loading = true;
                this.debouncedGetAnswer();
            }
        },
        created: function () {
            this.debouncedGetAnswer = _.debounce(this.getAnswer, 500);
        },
        methods: {
            getAnswer: function () {
                if (this.keyword == '') {
                    this.loading = false;
                    this.contacts = this.users;
                    return
                }
                var vm = this;
                axios
                .get('/api/search-messages', {
                    params: {
                        keyword: this.keyword
                    },
                    headers : {
                        'Accept' : 'application/json',
                        'Authorization' : 'Bearer ' + this.token,
                    } 
                })
                .then(function (response) {
                    vm.loading = false;
                    if(response.data.length == 0) {
                        vm.contacts = vm.users;
                    } else {
                        var arr = []
                        vm.users.forEach(function(contact){
                            response.data.forEach(function(item){
                                if(contact.id == item.id) {
                                    arr.push(contact)
                                }
                            });
                        });
                        vm.contacts = arr;
                    }
                })
                .catch(function (error) {
                    vm.loading = false;
                    vm.contacts = vm.users;
                    return
                })
            },
            deleteKeywords() {
                this.contacts = this.users;
                this.keyword = '';
            },
            dateFormat(date) {
                moment.locale("vi")
                if(moment(date).add(5, 'days') < moment()) {
                    return moment(date).format("DD MMM");
                } else {
                    return moment(date).format("ddd, HH:mm");
                }
            },
            selectMessages(index) {
                axios
                .get(`/api/get-messages-for/` + this.contacts[index].id, {
                    'headers' : {
                        'Accept' : 'application/json',
                        'Authorization' : 'Bearer '+ this.token,
                    }
                })
                .then((response) => {
                    this.messages = '';
                    this.messages = response.data.filter(item => item.text !== null);
                    this.contacts[index].unread = 0;
                    this.contact = this.contacts[index];
                })
            },
            scrollToBottom() {
                setTimeout(() => {
                    this.$refs.feed.scrollTop = this.$refs.feed.scrollHeight - this.$refs.feed.clientHeight;
                }, 50);
            },
            send(e) {
                e.preventDefault();
                if (this.message == '' || Object.keys(this.contact).length == 0) {
                    return;
                }
                axios.post('api/messages/send', {
                    contact_id: this.contact.id,
                    text: this.message
                }, {
                    'headers' : {
                        'Accept' : 'application/json',
                        'Authorization' : 'Bearer ' + this.token,
                    }
                }).then((response) => {
                    this.messages.push(response.data)
                    this.message = ''

                    this.contact.last_message = new Date().toISOString();
                    this.contacts = this.contacts.filter(item => item.id !== this.contact.id);
                    this.contacts.unshift(this.contact);
                })
            },
            sortContacts() {
                this.contacts = this.contacts.reverse()
            },
            isSendRealtime(message) {
                var index = this.contacts.findIndex(item => item.id == message.from_contact.id)
                var user = this.contacts[index];
                user.last_message = new Date().toISOString();
                this.contacts = this.contacts.filter(item => item.id !== message.from_contact.id);
                this.contacts.unshift(user);
                if (Object.keys(this.contact).length != 0 && message.from == this.contact.id) {
                    this.messages.push(message);
                    return;
                }

                this.updateUnreadCount(message.from_contact, false); //khi this.messages ko phải là Your.
                //message.from_contact là gì ko biết. tự sinh ra. là thông tin của người bên kia chat vs mình
            },
            updateUnreadCount(contact, reset) {
                this.contacts = this.contacts.map((single) => {
                    if (single.id !== contact.id) {
                        return single;
                    }
                    if (reset)
                        single.unread = 0;
                    else
                        single.unread += 1;
                    return single;
                })
            }
        },
    }
</script>
