<template>
<div style="padding-bottom:0.1px;">
    <div class="spin-wrapper" v-if="post == ''">
        <div class="spinner">
            <div class="rect1"></div>
            <div class="rect2"></div>
            <div class="rect3"></div>
            <div class="rect4"></div>
            <div class="rect5"></div>
        </div>
    </div>
    <div class="box-content shadow-sm" v-if="post == ''">
        <div class="row p-2 template-post">
            <div class="col d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center">
                    <div class="circle-img mr-2"></div>
                    <div class="p-0">
                        <div class="rounded-pill icon-user"></div>
                        <div class="rounded-pill icon-date"></div>
                    </div>
                </div>
                <i class="far fa-ellipsis-h"></i>
            </div>
            <div class="px-3 icon-content">
                <div class="rounded-pill w-50"></div>
                <div class="rounded-pill w-75"></div>
                <div class="rounded-pill w-25"></div>
            </div>
            <div class="px-3">
                <div class="d-flex align-items-center justify-content-between icon-ation pt-2">
                    <div class="d-flex">
                        <i class="far fa-heart"></i>
                        <i class="far fa-comment"></i>
                        <i class="far fa-share"></i>
                    </div>
                </div>
                <div class="d-flex align-items-center py-3 icon-comment">
                    <div class="circle-img"></div>
                    <div class="rounded-pill icon-input ml-auto"></div>
                </div>
            </div>
        </div>
    </div>

    <div class="box-content shadow-sm" v-else>
        <div class="row px-3 py-2 box-post">
            <!-- avatar + name -->
            <div class="col d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center">
                    <img :src="post.image_profile != '' ? post.image_profile : `/images/user.png`" style="width:35px; height:35px; vertical-align: middle;" class="rounded-circle img-fluid">
                    <!-- <h1 class="text-img" v-bind:style="{backgroundImage: post.image_profile != '' ? `url('` + post.image_profile + `')` : `url('/images/user.png')` }">{{ post.username | username() }}</h1> -->
                    <div>
                        <router-link :to="{name: 'profile', params: { username: post.username } }"><div class="name-user-post">{{post.username}}</div></router-link>
                        <p class="datatime-post">{{dateFormat(post.created_at)}}</p>
                    </div>
                </div>
                <div class="dropdown">
                    <h5 data-toggle="dropdown"><i class="far fa-ellipsis-h"></i></h5>
                    <div class="dropdown-menu dropdown-menu-right mt-1 px-2 shadow">
                        <i v-if="post.user_id == user.id" @click="editPost()" class="fas fa-edit dropdown-item"></i>
                        <i v-if="post.user_id == user.id" @click="deletePost()" class="fas fa-trash-alt dropdown-item"></i>
                        <i class="fas fa-link dropdown-item" @click="copyURL()"></i>
                    </div>
                </div>
            </div>
            <!-- action delete + edit -->
            <!-- content + image -->
            <div class="px-3 post-image">
                <span v-if="post.content != null">{{ post.content }}</span>
                <div class="mt-2 bg-images" v-if="post.path != ''" v-bind:style="{backgroundImage: `url('` + post.path + `')`}"></div>
            </div>
            <!-- icon like... -->
            <div class="px-3">
                <div class="d-flex align-items-center py-3 justify-content-between box-action-post">
                    <div class="d-flex">
                        <i v-if="post.likes" v-bind:class="[post.likes.includes(user.id) ? isLiked : '']" ref='ref_likes' v-on:click="likesPost()" class="far fa-heart"></i>
                        <label for="comment" class="px-5 m-0"><i class="far fa-comment"></i></label>
                        <i class="far fa-share"></i>
                    </div>
                    <div class="d-flex" v-if="post.likes || post.comments">
                        <p v-if="post.likes.length > 0" class="m-0">{{ post.likes.length }} likes</p>
                        <p v-if="post.comments.length> 0" class="m-0 pl-3">{{ post.comments.length }} comments</p>
                    </div>
                </div>
                <!-- input comment -->
                <div class="d-flex align-items-center pb-3">
                    <img :src="user.image_profile != '' ? user.image_profile : `/images/user.png`" style="width:35px; height:35px; vertical-align: middle;" class="rounded-circle img-fluid mr-3">
                    <!-- <h1 class="text-img pr-4" v-bind:style="{backgroundImage: user.image_profile != '' ? `url('` + user.image_profile + `')` : `url('/images/user.png')`}">{{ user.username | username() }}</h1> -->
                    <form @submit.prevent="addComment()" class="w-100">
                        <input type="text" id="comment" ref='ref_comment' placeholder="Add comment..." class="rounded-pill px-3 py-1 w-100 comment">
                        <button type="submit" class="btn btn-sm rounded-pill" hidden></button>
                    </form>
                </div>

                <div class="box-comment" v-if="post.comments">
                    <div class="d-flex py-2" v-for="(item, index) in post.comments" :key="index">
                        <img :src="item.user.image_profile != null ? `../storage/images/users/`+ item.user.id + `/image_profile/` + item.user.image_profile : `/images/user.png`" style="width:35px; height:35px; vertical-align: middle;" class="rounded-pill img-fluid">
                        <div class="d-flex align-items-center w-100 justify-content-between pl-3">
                            <div class="w-100">
                                <div class="d-flex align-items-center w-100 justify-content-between">
                                    <div>
                                        <p class="name-user-post m-0">{{item.user.username}}</p>
                                        <p class="m-0">{{item.comment}}</p>
                                    </div>
                                    <i class="far fa-heart px-0 ml-10" ref='liked' v-bind:class="[item.likes.includes(user.id) ? isLiked : '']" v-on:click="likesComment(index)"></i>
                                </div>
                                <div class="info-cm">
                                    <p>{{dateFormat(item.created_at)}}</p>
                                    <p v-if="item.likes != ''">{{ item.likes.length }} likes</p>
                                    <p class="reply" v-on:click="replyComment(index)">Reply</p>
                                    <p class="reply" v-if="item.reply > 0" v-on:click="seeTheAnswer(index)">See answer ({{ item.reply }})</p>
                                </div>
                                <!-- reply comment -->
                                <div class="d-flex align-items-center pt-1">
                                    <img v-if="item.active" :src="user.image_profile != '' ? user.image_profile : `/images/user.png`" style="width:30px; height:30px; vertical-align: middle;" class="img-fluid mr-3" :class="user.image_profile != '' ? 'border border-2' : ''">
                                    <form @submit.prevent="reply(index)" class="w-100">
                                        <input type="text" placeholder="Comment..." :hidden="item.active ? false : true" ref="reply" class="px-2 py-1 w-100 comment">
                                        <button type="submit" class="btn" hidden></button>
                                    </form>
                                </div>
                                <div v-if="item.replies != []">
                                    <div class="d-flex py-1" v-for="(reply, key) in item.replies" :key="key">
                                        <img :src="reply.user.image_profile != null ? `../storage/images/users/`+ reply.user.id + `/image_profile/` + reply.user.image_profile : `/images/user.png`" style="width:30px; height:30px; vertical-align: middle;" class="img-fluid mt-1 mr-2" :class="reply.user.image_profile != null ? 'border border-2' : ''">
                                        <div>
                                            <p class="name-user-post m-0">{{reply.user.username}}</p>
                                            <p class="m-0">{{reply.text}}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</template>
<script>
    import moment from 'moment';
    export default {
        metaInfo () {
            return {
                title: this.post.username != undefined && this.post.content != null ? '@' + this.post.username + ' | ' + this.post.content : 'ʟ ᴏ ɴ ᴇ ʟ ʏ',
            }
        },
        data() {
            return {
                user:this.$store.getters.getUser,
                post:[],
                isLiked: 'is-liked',
                replies: {}
            }
        },
        mounted() {
            axios
            .get('/api/post/'+this.$route.params.id+'/details')
            .then(response => {
                this.post = response.data
                console.log(this.post)
            })
            .catch(error => {
                this.$router.back()
            });
        },
        computed: {
        },
        methods: {
            dateFormat(date) {
                moment.locale("vi")
                if(moment(date).add(5, 'days') < moment()) {
                    return moment(date).format("DD MMM, YYYY");
                } else {
                    return moment(date).format("ddd, HH:mm");
                }
            },
            editPost() {
                this.$router.push({ name: 'home' })
            },
            deletePost() {
                axios
                .post('/api/post/destroy', {
                    id: this.post.id
                })
                .then((response) => {
                    this.$router.push({ name: 'home' })
                })
                .catch((error) => {
                    return
                });
            },
            likesPost() {
                axios
                .post('/api/post/likes', {
                    user_id:this.user.id,
                    post_id: this.post.id
                })
                .then((response) => {
                    if(response.data.likes) {
                        this.$refs.ref_likes.classList.add('is-liked')
                        this.post.likes.push(this.user.id)
                    } else {
                        this.$refs.ref_likes.classList.remove('is-liked')
                        this.post.likes = this.post.likes.filter(item => item !== this.user.id);
                    }
                })
                .catch((error) => {
                    return
                });
                // if(this.$refs.ref_likes.classList.contains('is-liked')) {
            },
            addComment() {
                if(this.$refs.ref_comment.value != '') {
                    if(!this.$store.getters.loggedIn) {
                        this.$router.push({ name: 'login' })
                    }
                    axios
                    .post('/api/post/comment', {
                        user_id:this.user.id,
                        post_id: this.post.id,
                        comment: this.$refs.ref_comment.value
                    })
                    .then((response) => {
                        this.post.comments.unshift(
                            {'comment':this.$refs.ref_comment.value,
                            'user_id':this.user.id,
                            'created_at': new Date().toISOString(),
                            'user': response.data.user,
                            'active': false,
                            'likes': [],
                            'reply': 0,
                            'id': response.data.comment_id
                            }
                        )
                        this.$refs.ref_comment.value = ''
                    })
                    .catch((error) => {
                        return
                    });
                }
            },
            likesComment(index) {
                axios
                .post('/api/likes-comment/'+ this.post.comments[index].id, {
                    id: this.user.id,
                })
                .then((response) => {
                    if(response.data.likes) {
                        this.$refs.liked[index].classList.add('is-liked')
                        this.post.comments[index].likes.push(this.user.id)
                    } else {
                        this.$refs.liked[index].classList.remove('is-liked')
                        const arr = this.post.comments[index].likes.filter(item => item !== this.user.id);
                        this.post.comments[index].likes = arr
                    }
                })
                .catch((error) => {
                    return
                });
            },
            replyComment(index) {
                if(this.post.comments[index].active) {
                    this.post.comments[index].active = false
                } else {
                    this.post.comments[index].active = true
                    this.$refs.reply[index].value = '@' + this.post.comments[index].user.username + ' '
                }
            },
            reply(index) {
                if(this.$refs.reply[index].value != '') {
                    if(!this.$store.getters.loggedIn) {
                        this.$router.push({ name: 'login' })
                    }
                    axios
                    .post('/api/reply-comment/'+ this.post.comments[index].id, {
                        id: this.user.id,
                        text: this.$refs.reply[index].value
                    })
                    .then((response) => {
                        // this.$refs.reply[index].value = ''
                        this.post.comments[index].reply += 1
                        this.post.comments[index].active = false
                    })
                    .catch((error) => {
                        return
                    });
                }
            },
            seeTheAnswer(index) {
                this.post.comments[index].active = false
                axios
                .get('/api/replies/'+ this.post.comments[index].id)
                .then((response) => {
                    this.post.comments[index].replies = response.data
                })
                .catch((error) => {
                    return
                });
            }
        }
    }
</script>