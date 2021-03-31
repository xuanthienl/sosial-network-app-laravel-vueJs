<template>
<div style="padding-bottom:0.1px;">
    <div class="spin-wrapper" v-if="posts == ''">
        <div class="spinner">
            <div class="rect1"></div>
            <div class="rect2"></div>
            <div class="rect3"></div>
            <div class="rect4"></div>
            <div class="rect5"></div>
        </div>
    </div>

    <div class="box-content shadow-sm bg-white">
        <form @submit.prevent="submitPost" class="form-post p-2" enctype="multipart/form-data" ref='create_post'>
            <div class="d-flex">
                <!-- <div class="mr-3">
                    <h1 class="text-img" v-bind:style="{backgroundImage: user.image_profile != '' ? `url('` + user.image_profile + `')` : `url('/images/user.png')` }">{{ user.username | username() }}</h1>
                </div> -->
                <img :src="user.image_profile != '' ? user.image_profile : `/images/user.png`" style="width:35px; height:35px; vertical-align: middle;" class="rounded-circle img-fluid mr-3">
                <textarea class="form-control" ref="content" rows="2" v-model="content"></textarea>
            </div>
            <div v-if="image != ''" class="bg-images my-2" v-bind:style="{backgroundImage: `url('` + image + `')`}" style="max-height:250px;"></div>
            <div class="d-flex justify-content-between align-items-center mt-2">
                <div class="image-upload">
                    <label for="file-input" class="m-0"><i class="far fa-images p-0"></i></label>
                    <input id="file-input" v-on:change="onImageChange" type="file"/>
                </div>
                <div class="shadow-sm btn-submit"><input type="submit" value="Post"></div>
            </div>
        </form>
    </div>

    <div v-if="posts == ''">
        <div class="box-content shadow-sm" v-for="a in 2" :key="a">
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
    </div>

    <div class="box-content shadow-sm" v-for="(post, index) in items" :key="index">
        <div class="row p-2 box-post">
            <div class="col d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center">
                    <img :src="post.image_profile != '' ? post.image_profile : `/images/user.png`" style="width:35px; height:35px; vertical-align: middle;" class="rounded-circle img-fluid">
                    <div>
                        <router-link :to="{name: 'profile', params: { username: post.username } }"><div class="name-user-post">{{post.username}}</div></router-link>
                        <p class="datatime-post">{{dateFormat(post.created_at)}}</p>
                    </div>
                </div>
                <div class="dropdown">
                    <i class="far fa-ellipsis-h" data-toggle="dropdown"></i>
                    <div class="dropdown-menu dropdown-menu-right mt-1 px-2 shadow">
                        <i v-if="post.user_id == user.id" @click="editPost(index)" class="fas fa-edit dropdown-item"></i>
                        <i v-if="post.user_id == user.id" @click="deletePost(index)" class="fas fa-trash-alt dropdown-item"></i>
                        <i class="fas fa-link dropdown-item" @click="copyURL(index)"></i>
                    </div>
                </div>
            </div>
            <div class="px-3 post-image">
                <span v-if="post.content != null" v-on:click="detailsPost(index)">{{ post.content | shortText(250) }}</span>
                <span class="d-none" v-if="post.content != null" v-on:click="detailsPost(index)">{{ post.content }}</span>
                <div v-if="post.content != null && post.content.length > 250" v-on:click="toggler($event)" class="see-more">See more</div>
                <div class="mt-2 bg-images" v-if="post.path != ''" v-on:click="detailsPost(index)" v-bind:style="{backgroundImage: `url('` + post.path + `')`}"></div>
            </div>
            <div class="px-3">
                <div class="d-flex align-items-center justify-content-between py-3 box-action-post">
                    <div class="d-flex">
                        <i v-bind:class="[post.likes.includes(user.id) ? isLiked : '']" ref='ref_likes' v-on:click="likesPost(index)" class="far fa-heart"></i>
                        <label v-bind:for="'comment' + index" class="px-5 m-0"><i class="far fa-comment"></i></label>
                        <i class="far fa-share" v-on:click="sharePost(index)"></i>
                    </div>
                    <div class="d-flex">
                        <p v-if="post.likes.length > 0" class="m-0">{{ post.likes.length }} likes</p>
                        <p v-if="post.comments.length> 0" class="m-0 pl-3" v-on:click="detailsPost(index)">{{ post.comments.length }} comments</p>
                    </div>
                </div>
                <div class="d-flex align-items-center pb-2">
                    <img :src="user.image_profile != '' ? user.image_profile : `/images/user.png`" style="width:35px; height:35px; vertical-align: middle;" class="rounded-circle img-fluid mr-3">
                    <form @submit.prevent="addComment(index)" class="w-100">
                        <input type="text" v-bind:id="'comment' + index" ref='ref_comment' placeholder="Add comment..." class="rounded-pill px-3 py-1 w-100 comment">
                        <button type="submit" class="btn btn-sm rounded-pill" hidden></button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <!-- Modal -->
    <div class="modal" ref="modal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
        <div class="modal-dialog modal-sm modal-custom">
            <div class="modal-content text-center py-3">
                <h4 class="modal-title py-4">itempty!</h4>
                <div class="d-flex justify-content-evenly">
                    <p v-on:click="cancelEdit()">cancel</p>
                    <p v-on:click="continueEdit()">continue</p>
                </div>
            </div>
        </div>
    </div>
    <!-- Modal Create Post -->
    <div class="modal box-create-post" ref="modalCreatePost" data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog modal-lg modal-custom">
            <div class="modal-content px-4 py-3">
                <div class="modal-header p-0">
                    <h5 class="modal-title"><i class="fas fa-feather-alt mr-1" style="color:#ff7e67;"></i>create...</h5>
                    <i class="fas fa-times" v-on:click="closeModal()"></i>
                </div>
                <form @submit.prevent="submitPostModal" class="form-post" enctype="multipart/form-data">
                    <div class="modal-body my-3">
                        <textarea class="form-control" rows="3" v-model="content_modal"></textarea>
                        <div v-if="image_modal != ''" class="bg-images mt-3" v-bind:style="{backgroundImage: `url('` + image_modal + `')`}" style="max-height:250px;"></div>
                    </div>
                    <div class="modal-footer p-0 d-flex justify-content-between">
                        <div class="image-upload">
                            <label for="file-input-modal" class="m-0"><i class="far fa-images"></i></label>
                            <input id="file-input-modal" v-on:change="onImageChangeModal" type="file"/>
                        </div>
                        <div class="shadow-sm btn-submit"><input type="submit" value="Post"></div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <!-- Modal Show Post -->
    <div class="modal show-post" ref="modalShowPost">
        <i class="fa fa-times sticky-top position-fixed icon-close" v-on:click="close()"></i>
        <div class="modal-dialog modal-xl modal-dialog-centered px-5 p-sm-0">
            <div class="modal-content box-pc d-none d-lg-block">
                <div class="modal-body d-flex" v-if="info != ''">
                    <div class="w-75 box-image" v-bind:style="{backgroundImage: `url('` + info.path + `')`}"></div>
                    <div class="w-25 overflow-scroll box-info">
                        <div class="d-flex justify-content-between align-items-center pb-2">
                            <div class="d-flex align-items-center">
                                <img :src="info.image_profile != '' ? info.image_profile : `/images/user.png`" style="width:35px; height:35px; vertical-align: middle;" :class="info.image_profile != '' ? 'border border-2' : ''" class="img-fluid">
                                <div>
                                    <div class="name-user-post">{{info.username}}</div>
                                    <p class="datatime-post">{{dateFormat(info.created_at)}}</p>
                                </div>
                            </div>
                            <i class="far fa-ellipsis-h" data-toggle="dropdown"></i>
                            <div class="dropdown-menu dropdown-menu-right shadow-sm">
                                <i v-if="info.user_id == user.id" @click="editPost(info_index)" class="fas fa-edit dropdown-item"></i>
                                <i v-if="info.user_id == user.id" @click="deletePost(info_index)" class="fas fa-trash-alt dropdown-item"></i>
                                <i class="fas fa-link dropdown-item" @click="copyURL(info_index)"></i>
                            </div>
                        </div>

                        <p class="m-0 p-content pb-2" v-if="info.content != null">{{info.content}}</p>
                        
                        <div class="d-flex pt-2 box-action" v-if="info != ''">
                            <i :ref="'ref_likes' + info_index" v-bind:class="[info.likes.includes(user.id) ? isLiked : '']" v-bind:style="{color: info.likes.includes(user.id) ? '#ec524b' : ''}" v-on:click="likesPost(info_index)" class="far fa-heart"></i>
                            <label for="comment"><i class="far fa-comment px-4"></i></label>
                            <i class="far fa-share"></i>
                        </div>

                        <div class="d-flex pb-2" v-if="info.likes.length > 0 || info.comments.length > 0">
                            <p class="m-0 pr-3" v-if="info.likes.length > 0">{{ info.likes.length }} likes</p>
                            <p class="m-0" v-if="info.comments.length > 0">{{ info.comments.length }} comments</p>
                        </div>

                        <div class="d-flex align-items-center pb-2">
                            <img :src="user.image_profile != '' ? user.image_profile : `/images/user.png`" style="width:35px; height:35px; vertical-align: middle;" :class="user.image_profile != '' ? 'border border-2' : ''" class="img-fluid mr-2">
                            <form @submit.prevent="modalComment(info_index)" class="w-100">
                                <input type="text" id="comment" ref="comment" placeholder="Add comment..." class="w-100 border border-2">
                                <button type="submit" class="btn btn-sm rounded-pill" hidden></button>
                            </form>
                        </div>

                        <div class="box-comment" v-if="info.comments.length > 0">
                            <div class="d-flex pb-2" v-for="(item, index) in info.comments" :key="index">
                                <img :src="item.user.image_profile != null ? `../storage/images/users/`+ item.user.id + `/image_profile/` + item.user.image_profile : `/images/user.png`" :class="item.user.image_profile != null ? 'border border-2' : ''" style="width:35px; height:35px; vertical-align: middle;" class="img-fluid mt-1">
                                <div class="d-flex align-items-center w-100 justify-content-between pl-3">
                                    <div class="w-100">
                                        <div class="d-flex align-items-center w-100 justify-content-between">
                                            <div>
                                                <p class="name-user-post m-0">{{item.user.username}}</p>
                                                <p class="m-0">{{item.comment}}</p>
                                            </div>
                                            <i class="far fa-heart px-0 ml-10" ref='liked' v-bind:class="[item.likes.includes(user.id) ? isLiked : '']" v-on:click="likesComment(info_index, index)"></i>
                                        </div>
                                        <div class="info-cm">
                                            <p>{{dateFormat(item.created_at)}}</p>
                                            <p v-if="item.likes != ''">{{ item.likes.length }} likes</p>
                                            <p class="reply" v-on:click="replyComment(info_index, index)">Reply</p>
                                        </div>
                                        <div class="info-cm">
                                            <p class="reply" v-if="item.reply > 0" v-on:click="seeTheAnswer(info_index, index)"><i class="fas fa-angle-right"></i> See answer ({{ item.reply }})</p>
                                        </div>
                                        <div class="d-flex align-items-center pt-1">
                                            <img v-if="item.active" :src="user.image_profile != '' ? user.image_profile : `/images/user.png`" style="width:30px; height:30px; vertical-align: middle;" class="img-fluid mr-3" :class="user.image_profile != '' ? 'border border-2' : ''">
                                            <form @submit.prevent="reply(info_index, index)" class="w-100">
                                                <input type="text" placeholder="Comment..." :hidden="item.active ? false : true" ref="reply" class="p-1 w-100 border border-2">
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
            <div class="modal-content box-sp d-block d-lg-none">
                <div class="modal-body" v-if="info != ''">
                    <div class="box-image" v-bind:style="{backgroundImage: `url('` + info.path + `')`}"></div>
                    <div class="box-info px-0" style="background:#fff; height:auto">
                        <div>
                            <div class="d-flex justify-content-between align-items-center">
                                <div class="d-flex align-items-center">
                                    <img :src="info.image_profile != '' ? info.image_profile : `/images/user.png`" style="width:35px; height:35px; vertical-align: middle;" :class="info.image_profile != '' ? 'border border-2' : ''" class="img-fluid">
                                    <div>
                                        <div class="name-user-post">{{info.username}}</div>
                                        <p class="datatime-post">{{dateFormat(info.created_at)}}</p>
                                    </div>
                                    <i class="far fa-ellipsis-h ml-3" data-toggle="dropdown"></i>
                                    <div class="dropdown-menu dropdown-menu-right shadow-sm">
                                        <i v-if="info.user_id == user.id" @click="editPost(info_index)" class="fas fa-edit dropdown-item"></i>
                                        <i v-if="info.user_id == user.id" @click="deletePost(info_index)" class="fas fa-trash-alt dropdown-item"></i>
                                        <i class="fas fa-link dropdown-item" @click="copyURL(info_index)"></i>
                                    </div>
                                </div>
                                <div class="d-flex align-items-center box-action" v-if="info != ''">
                                    <i :ref="'ref_likes' + info_index" v-bind:class="[info.likes.includes(user.id) ? isLiked : '']" v-bind:style="{color: info.likes.includes(user.id) ? '#ec524b' : ''}" v-on:click="likesPost(info_index)" class="far fa-heart"></i>
                                    <i class="far fa-share"></i>
                                </div>
                            </div>
                            <div class="d-flex pt-2" v-if="info.likes.length > 0 || info.comments.length > 0">
                                <p class="m-0 pr-3" v-if="info.likes.length > 0">{{ info.likes.length }} likes</p>
                                <p class="m-0 action-details-post" v-if="info.comments.length > 0" v-on:click="pagePost(info_index)">{{ info.comments.length }} comments</p>
                            </div>
                        </div>
                        <div class="d-flex align-items-center">
                            <img :src="user.image_profile != '' ? user.image_profile : `/images/user.png`" style="width:35px; height:35px; vertical-align: middle;" :class="user.image_profile != '' ? 'border border-2' : ''" class="img-fluid mr-2">
                            <form @submit.prevent="modalCommentSP(info_index)" class="w-100">
                                <input type="text" id="comment" ref="comment_sp" placeholder="Add comment..." class="w-100 border border-2">
                                <button type="submit" class="btn btn-sm rounded-pill" hidden></button>
                            </form>
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
                title: 'ʟ ᴏ ɴ ᴇ ʟ ʏ',
            }
        },
        data() {
            return {
                user:this.$store.getters.getUser,
                content: '',
                image: '',
                content_modal: '',
                image_modal: '',
                posts: [],
                isLiked: 'is-liked',
                isEdit: null,
                info: [],
                info_index: '',
            }
        },
        mounted() {
            this.axios
            .get('/api/post/index')
            .then(response => {
                this.posts = response.data.posts
            })
            .catch(response => {
            });

            // open Modal Create Post New
            if(this.$route.query.action == 'create') {
                $(this.$refs.modalCreatePost).modal('show')
            };
        },
        watch: {
            $route(to) {
                // open Modal Create Post New
                if(to.query.action == 'create') {
                    $(this.$refs.modalCreatePost).modal('show');
                }
                // reload page home with list Posts
                if(to.query.action == 'scroll') {
                    let query = Object.assign({}, this.$route.query);
                    delete query.action;
                    this.$router.replace({ query });
                    this.$refs['create_post'].scrollIntoView(0,0);
                    this.axios
                    .get('/api/post/index')
                    .then(response => {
                        this.posts = response.data.posts
                    })
                    .catch(response => {
                    });
                }
            }
        },
        computed: {
            items() {
                return this.posts.reverse()
            }
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
            detailsPost(index) {
                if(this.posts[index].path == '') {
                    var id = this.posts[index].id;
                    this.$router.push({ name: 'post-details', params: { id } }).catch(()=>{});
                } else {
                    $(this.$refs.modalShowPost).modal('show');
                    this.info = this.posts[index]
                    this.info_index = index
                }

                // var i = new Image(); 
                // i.onload = function(){
                //     // console.log(i.width, i.height)
                // };
                // i.src = this.info.path; 
                // this.matchHeight(i.width,i.height);
            },
            pagePost(index) {
                var id = this.posts[index].id;
                this.$router.push({ name: 'post-details', params: { id } }).catch(()=>{});
                $(this.$refs.modalShowPost).modal('toggle');
            },
            close() {
                $(this.$refs.modalShowPost).modal('toggle');
                this.info = ''
                this.info_index = ''
            },
            onImageChangeModal(e){
                if (e.target.files[0]) {
                    let data = this
                    if (e.target.files[0].type.slice(0, 6) === 'image/') {
                        var reader = new FileReader();
                        reader.onload = function (e) {
                            e.preventDefault();
                            e.stopPropagation();
                            data.image_modal = e.target.result;
                        }
                        reader.readAsDataURL(e.target.files[0]);
                    }
                }
            },
            submitPostModal(e) {
                e.preventDefault();
                if(this.image_modal != '' || this.content_modal != '') {
                    if(!this.$store.getters.loggedIn) {
                        this.$router.push({ name: 'login' })
                    }
                    axios.post('/api/post/store', {
                        user_id: this.user.id,
                        content: this.content_modal,
                        image: this.image_modal,
                    })
                    .then((response) => {
                        this.posts.push({
                            'id':response.data.id,
                            'username':this.user.username,
                            'content':this.content_modal,
                            'path':this.image_modal,
                            'comments': [],
                            'likes': [],
                            'created_at': new Date().toISOString(),
                            'user_id':this.user.id,
                            'image_profile': this.user.image_profile
                        })
                        this.image_modal = '';
                        this.content_modal = '';
                        //detele query router
                        let query = Object.assign({}, this.$route.query);
                        delete query.action;
                        this.$router.replace({ query });
                        $(this.$refs.modalCreatePost).modal('toggle');
                        this.$refs['create_post'].scrollIntoView(0,0)

                        this.$notify({
                            group: 'foo',
                            title: 'Created a Successfully post!',
                            duration: 10000,
                            speed: 1000
                        });
                    })
                    .catch((error) => {
                        //detele query router
                        let query = Object.assign({}, this.$route.query);
                        delete query.action;
                        this.$router.replace({ query });
                        $(this.$refs.modalCreatePost).modal('toggle');
                        return
                    });
                }
            },
            closeModal() {
                if (this.$route.query.action == 'create') {
                    //detele query router
                    let query = Object.assign({}, this.$route.query);
                    delete query.action;
                    this.$router.replace({ query });
                    this.image_modal = '';
                    this.content_modal = '';
                    $(this.$refs.modalCreatePost).modal('toggle');
                    return
                }
            },
            // End Modal Create Post
            onImageChange(e){
                if (e.target.files[0]) {
                    let data = this
                    if (e.target.files[0].type.slice(0, 6) === 'image/') {
                        var reader = new FileReader();
                        reader.onload = function (e) {
                            e.preventDefault();
                            e.stopPropagation();
                            data.image = e.target.result;
                        }
                        reader.readAsDataURL(e.target.files[0]);
                    }
                }
            },
            submitPost(e) {
                e.preventDefault();
                if(!this.$store.getters.loggedIn) {
                    this.$router.push({ name: 'login' })
                }
                if(this.isEdit != null) {
                    if(this.content == '' && this.image == '') {
                        $(this.$refs.modal).modal('show')
                    } else {
                        axios.post('/api/post/'+ this.posts[this.isEdit].id +'/update', {
                            content: this.content,
                            image: this.image,
                        })
                        .then((response) => {
                            this.posts[this.isEdit].content = this.content
                            this.posts[this.isEdit].path = this.image
                            this.image = ''
                            this.content = ''
                            this.isEdit = null

                            this.$notify({
                                group: 'foo',
                                title: 'Update post Successfully!',
                                duration: 10000,
                                speed: 1000
                            });
                        })
                        .catch((error) => {
                            return
                        });
                    }
                } else {
                    if(this.image != '' || this.content != '') {
                        axios.post('/api/post/store', {
                            user_id: this.user.id,
                            content: this.content,
                            image: this.image,
                        })
                        .then((response) => {
                            this.posts.push({
                                'id':response.data.id,
                                'username':this.user.username,
                                'content':this.content,
                                'path':this.image,
                                'comments': [],
                                'likes': [],
                                'created_at': new Date().toISOString(),
                                'user_id':this.user.id,
                                'image_profile': this.user.image_profile
                            })
                            this.image = ''
                            this.content = ''

                            this.$notify({
                                group: 'foo',
                                title: 'Created a Successfully post!',
                                duration: 10000,
                                speed: 1000
                            });
                        })
                        .catch((error) => {
                            if(error.response) {
                                console.log(error.response.data.message);
                            } else {
                                console.log(error)
                            }
                        });
                    }
                }
            },
            editPost(index) {
                if($(this.$refs.modalShowPost).hasClass('show')) {
                    $(this.$refs.modalShowPost).modal('toggle');
                }
                this.image = this.posts[index].path
                this.content = this.posts[index].content
                this.$refs['create_post'].scrollIntoView(0,0)
                this.isEdit = index
            },
            cancelEdit() {
                this.image = ''
                this.content = ''
                this.isEdit = null
                $(this.$refs.modal).modal('toggle')
            },
            continueEdit() {
                $(this.$refs.modal).modal('toggle')
                this.$refs['content'].focus()
            },
            deletePost(index) {
                axios
                .post('/api/post/destroy', {
                    id: this.posts[index].id
                })
                .then((response) => {
                    this.posts.splice(index, 1)
                    if($(this.$refs.modalShowPost).hasClass('show')) {
                        $(this.$refs.modalShowPost).modal('toggle');
                    }
                    const text = `Date: ${moment().calendar()}`
                    this.$notify({
                        group: 'foo',
                        title: 'Deleted Successfully!',
                        text,
                        duration: 10000,
                        speed: 1000
                    });
                })
                .catch((error) => {
                    return
                });

            },
            copyURL(index) {
                const path = this.$router.resolve({
                    name: "post-details",
                    params: { id: this.posts[index].id }
                }).href;
                const fullUrl = window.location.origin + path;
                navigator.clipboard.writeText(fullUrl);

                this.$notify({
                    group: 'foo',
                    title: 'Copied Successfully!',
                    duration: 10000,
                    speed: 1000
                });
            },
            sharePost(index) {
                const path = this.$router.resolve({
                    name: "post-details",
                    params: { id: this.posts[index].id }
                }).href;
                const fullUrl = window.location.origin + path;
                window.open(`https://www.facebook.com/sharer?u=` + fullUrl, '_blank');
            },
            likesPost(index) {
                axios
                .post('/api/post/likes', {
                    user_id:this.user.id,
                    post_id: this.posts[index].id
                })
                .then((response) => {
                    if(response.data.likes) {
                        this.$refs.ref_likes[index].classList.add('is-liked')
                        this.posts[index].likes.push(this.user.id)
                    } else {
                        this.$refs.ref_likes[index].classList.remove('is-liked')
                        this.posts[index].likes = this.posts[index].likes.filter(item => item !== this.user.id);
                    }
                })
                .catch((error) => {
                    return
                });
            },
            addComment(index) {
                if(this.$refs.ref_comment[index].value != '') {
                    if(!this.$store.getters.loggedIn) {
                        this.$router.push({ name: 'login' })
                    }
                    axios
                    .post('/api/post/comment', {
                        user_id:this.user.id,
                        post_id: this.posts[index].id,
                        comment: this.$refs.ref_comment[index].value
                    })
                    .then((response) => {
                        this.posts[index].comments.unshift(
                            {'comment':this.$refs.ref_comment[index].value,
                            'user_id':this.user.id,
                            'created_at': new Date().toISOString(),
                            'user': response.data.user,
                            'active': false,
                            'likes': [],
                            'reply': 0,
                            'id': response.data.comment_id
                            }
                        )
                        this.$refs.ref_comment[index].value = ''
                    })
                    .catch((error) => {
                        return
                    });
                }
            },
            modalComment(index) {
                if(this.$refs.comment.value != '') {
                    if(!this.$store.getters.loggedIn) {
                        this.$router.push({ name: 'login' })
                    }
                    axios
                    .post('/api/post/comment', {
                        user_id:this.user.id,
                        post_id: this.posts[index].id,
                        comment: this.$refs.comment.value
                    })
                    .then((response) => {
                        this.posts[index].comments.unshift(
                            {'comment':this.$refs.comment.value,
                            'user_id':this.user.id,
                            'created_at': new Date().toISOString(),
                            'user': response.data.user,
                            'active': false,
                            'likes': [],
                            'reply': 0,
                            'id': response.data.comment_id
                            }
                        )
                        this.$refs.comment.value = ''
                    })
                    .catch((error) => {
                        return
                    });
                }
            },
            modalCommentSP(index) {
                if(this.$refs.comment_sp.value != '') {
                    if(!this.$store.getters.loggedIn) {
                        this.$router.push({ name: 'login' })
                    }
                    axios
                    .post('/api/post/comment', {
                        user_id:this.user.id,
                        post_id: this.posts[index].id,
                        comment: this.$refs.comment_sp.value
                    })
                    .then((response) => {
                        this.posts[index].comments.unshift(
                            {'comment':this.$refs.comment_sp.value,
                            'user_id':this.user.id,
                            'created_at': new Date().toISOString(),
                            'user': response.data.user,
                            'active': false,
                            'likes': [],
                            'reply': 0,
                            'id': response.data.comment_id
                            }
                        )
                        this.$refs.comment_sp.value = ''
                    })
                    .catch((error) => {
                        return
                    });
                }
            },
            toggler(e) {
                if(e.target.innerHTML == 'See more') {
                    e.target.innerHTML = 'Less more';
                    e.target.parentElement.children[0].className = '';
                    e.target.parentElement.children[0].className = 'd-none';
                    e.target.parentElement.children[1].className = '';
                    e.target.parentElement.children[1].className = 'd-block';
                } else {
                    e.target.innerHTML = 'See more';
                    e.target.parentElement.children[0].className = '';
                    e.target.parentElement.children[0].className = 'd-block';
                    e.target.parentElement.children[1].className = '';
                    e.target.parentElement.children[1].className = 'd-none';
                }
            },
            likesComment(info_index, index) {
                axios
                .post('/api/likes-comment/'+ this.posts[info_index].comments[index].id, {
                    id: this.user.id,
                })
                .then((response) => {
                    if(response.data.likes) {
                        this.$refs.liked[index].classList.add('is-liked')
                        this.posts[info_index].comments[index].likes.push(this.user.id)
                    } else {
                        this.$refs.liked[index].classList.remove('is-liked')
                        const arr = this.posts[info_index].comments[index].likes.filter(item => item !== this.user.id);
                        this.posts[info_index].comments[index].likes = arr
                    }
                })
                .catch((error) => {
                    return
                });
            },
            replyComment(info_index, index) {
                if(this.posts[info_index].comments[index].active) {
                    this.posts[info_index].comments[index].active = false
                } else {
                    this.posts[info_index].comments[index].active = true
                    this.$refs.reply[index].value = '@' + this.posts[info_index].comments[index].user.username + ' '
                }
            },
            reply(info_index, index) {
                if(this.$refs.reply[index].value != '') {
                    if(!this.$store.getters.loggedIn) {
                        this.$router.push({ name: 'login' })
                    }
                    axios
                    .post('/api/reply-comment/'+ this.posts[info_index].comments[index].id, {
                        id: this.user.id,
                        text: this.$refs.reply[index].value
                    })
                    .then((response) => {
                        this.posts[info_index].comments[index].reply += 1
                        this.posts[info_index].comments[index].active = false
                    })
                    .catch((error) => {
                        return
                    });
                }
            },
            seeTheAnswer(info_index, index) {
                this.posts[info_index].comments[index].active = false
                axios
                .get('/api/replies/'+ this.posts[info_index].comments[index].id)
                .then((response) => {
                    this.posts[info_index].comments[index].replies = response.data
                })
                .catch((error) => {
                    return
                });
            }
        },
        filters: {
            shortText(value, limit) {
                if(value) {
                    if(value.length >= 250) {
                        return value.substr(0,value.indexOf(' ')+limit) + '...';
                    } else {
                        return value;
                    }
                }
            },
            username(value) {
                return value.substr(value.length - 2, value.length);
            }
        }
    }
</script>