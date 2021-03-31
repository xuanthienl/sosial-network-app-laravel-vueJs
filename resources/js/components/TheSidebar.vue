<template>
    <div class="d-none d-lg-block sticky-top box-main-right">
        <div class="rounded-pill shadow-sm px-4 w-100 d-flex align-items-center position-relative search-box" v-if="$route.name != 'music'">
            <div class="d-flex align-items-center dropdown w-100">
                <input type="text" placeholder="your keywords." v-model="keyword" class="w-100" data-toggle="dropdown">
                <div class="item loading-3" v-if="loading">
                    <div class="loading"></div>
                </div>
                <i class="fas fa-times" v-else-if="isKeywords" v-on:click="deleteKeywords()"></i>
                <button type="button" v-else><i class="fa fa-search" aria-hidden="true"></i></button>

                <div class="dropdown-menu w-100 shadow px-3 overflow-scroll">
                    <div class="d-flex align-items-center p-2" v-if="data == '' && answer != null">
                        <p>{{answer}}</p>
                    </div>
                    <router-link :to="{name: 'profile', params: { username: item.username } }" class="rounded-pill d-flex align-items-center p-2 m-1" v-else v-for="(item, index) in data" :key='index'>
                        <img :src="item.image_profile != null ? `../storage/images/users/`+ item.id + `/image_profile/` + item.image_profile : `/images/user.png`" class="img-fluid">
                        <p>{{ item.username }}</p>
                    </router-link>
                </div>
            </div>
        </div>
        <div v-if="users.length >= 1 && $route.name != 'music'" class="shadow-sm my-4 box-content box-followes">
            <p class="text px-2">maybe you...know !</p>
            <div v-for="(member, index) in users" :key="index" class="p-2 d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center">
                    <img :src="member.image_profile != null ? `../storage/images/users/`+ member.id + `/image_profile/` + member.image_profile : `/images/user.png`" class="img-fluid">
                    <router-link :to="{name: 'profile', params: { username: member.username } }">
                        <p class="username">{{member.username}}</p>
                    </router-link>
                </div>
                <p class="btn-follow" v-on:click="follow(index, $event)">follow</p>
            </div>
        </div>
        <div class="shadow-sm box-content tracks" v-if="tracks != '' " v-bind:style="{height: $route.name == 'music' ? '100%' : 'auto'}">
            <div class="d-flex p-2">
                <div class="w-100">
                    <div class="d-flex justify-content-between align-self-center">
                        <p class="m-0">{{tracks.title}}</p>
                        <i class="fas fa-heart"></i>
                    </div>
                    <p class="m-0">{{tracks.artists}}</p>
                </div>
            </div>

            <div class="tracks-action text-center p-2">
                <input type="range" ref="timeline" value="0">
                <div class="d-flex align-items-center justify-content-evenly">
                    <i @click="pre()" class="fas fa-fast-backward"></i>
                    <div v-if="isLoadFile" class="spinner-grow spinner-grow-sm"></div>
                    <div v-else>
                        <i @click="pause()" class="fas fa-pause icon-play" v-if="isPlay"></i>
                        <i @click="play()" class="fas fa-play icon-play" v-else></i>
                    </div>
                    <i @click="next()" class="fas fa-fast-forward"></i>
                </div>
                <p class="m-0 py-2" v-if="isLoadFile">is loading file</p>
            </div>
            <audio ref="tracks" controls hidden>
                <source :src="base64" type="audio/mpeg">
                <source :src="base64" type="audio/ogg">
            </audio>
        </div>
    </div>
</template>

<script>
    export default {
        data() {
            return {
                user: this.$store.getters.getUser,
                users: [],
                keyword: '',
                loading: false,
                isKeywords: false,
                answer: null,
                data: [],

                songs: Object.keys(this.$store.getters.getSongs).length === 0 ? {} : this.$store.getters.getSongs,
                tracks: this.$store.getters.getTracks == '' ? '' : this.$store.getters.getTracks,
                isPlay: false,
                base64: '',
                isLoadFile: false,
            }
        },
        mounted() {
            this.axios
            .get('/api/users', {
                params: { id: this.user.id }
            })
            .then(response => {
                const items = response.data.filter(user => user.id != this.user.id);
                this.users = items;
            })
            .catch(response => {
                return
            });

            // music
            if(this.tracks != '') {
                this.addAudioBase64(this.tracks.song)
                this.isLoadFile = true
            }
            
            if(this.tracks != '') {
                // sự kiện khi audio phát xong bài hát
                var self = this;
                if(Object.keys(self.songs).length === 0) {
                    self.songs = this.$store.getters.getSongs
                }
                self.$refs.tracks.addEventListener('ended',function(e){
                    self.isPlay = false
                    var isPlaylist = self.songs.find(item => item.id == self.tracks.id);
                    if(typeof isPlaylist != 'undefined') {
                        var rank = Object.keys(self.songs).find(key => self.songs[key].id === self.tracks.id);
                        if(parseInt(rank) + 1 == self.songs.length) {
                            self.tracks = self.songs[0]
                        } else {
                            self.tracks = self.songs.slice(parseInt(rank) + 1, parseInt(rank) + 2)[0];
                        }
                        self.$store.dispatch('addTracks', self.tracks)
                    }
                    self.addAudioBase64AndPlay(self.tracks.song)
                });
            }
        },
        watch: {
            keyword: function (newKeyword, oldKeyword) {
                this.loading = true;
                this.debouncedGetAnswer();
            },
            // music
            '$store.state.tracks': function() {
                if(this.$store.state.tracks != '') {
                    if(this.$store.state.tracks.id != this.tracks.id) {
                        if(this.$refs.tracks) {
                            this.$refs.tracks.pause();
                        }
                        this.isLoadFile = true;
                        this.tracks = this.$store.state.tracks
                        this.addAudioBase64AndPlay(this.$store.state.tracks.song)
                    }
                }
            },
            // '$store.state.is_new': function() {
            //     if(this.$store.state.is_new == true) {
            //         this.tracks = this.$store.state.songs[0]
            //         this.addAudioBase64AndPlay(this.$store.state.tracks.song)
            //         this.$store.dispatch('addTracks', this.$store.state.songs[0])
            //         this.$store.dispatch('isNew')
            //     }
            // }
        },
        created: function () {
            this.debouncedGetAnswer = _.debounce(this.getAnswer, 500);
        },
        methods: {
            addAudioBase64AndPlay(file) {
                axios.get('/api/music/convert-base64', {
                    params: {
                        file: file
                    }
                })
                .then((response) => {
                    this.base64 = response.data
                    this.$refs.tracks.onloadedmetadata = () => this.$refs.timeline.max = this.$refs.tracks.duration
                    this.$refs.tracks.ontimeupdate = () => this.$refs.timeline.value = this.$refs.tracks.currentTime
                    this.$refs.timeline.onchange = () => this.$refs.tracks.currentTime = this.$refs.timeline.value
                    this.$refs.tracks.pause();
                    this.$refs.tracks.load();
                    this.$refs.tracks.oncanplaythrough = this.$refs.tracks.play();
                    this.isPlay = true;
                    this.isLoadFile = false;
                })
                .catch((error) => {
                    return
                });
            },
            addAudioBase64(file) {
                axios.get('/api/music/convert-base64', {
                    params: {
                        file: file
                    }
                })
                .then((response) => {
                    this.base64 = response.data
                    this.$refs.tracks.onloadedmetadata = () => this.$refs.timeline.max = this.$refs.tracks.duration
                    this.$refs.tracks.ontimeupdate = () => this.$refs.timeline.value = this.$refs.tracks.currentTime
                    this.$refs.timeline.onchange = () => this.$refs.tracks.currentTime = this.$refs.timeline.value
                    this.$refs.tracks.pause();
                    this.$refs.tracks.load();
                    this.isLoadFile = false;
                })
                .catch((error) => {
                    return
                });
            },
            getAnswer: function () {
                if (this.keyword == '') {
                    this.loading = false;
                    this.isKeywords = false;
                    this.data = [];
                    this.answer = null;
                    return
                }
                var vm = this;
                axios
                .get('/api/search', {
                    params: {
                        keyword: this.keyword
                    }
                })
                .then(function (response) {
                    vm.loading = false;
                    vm.isKeywords = true;
                    vm.data = response.data;
                    if(vm.data.length == 0) {
                        vm.answer = 'No results found.';
                    }
                })
                .catch(function (error) {
                    return
                })
            },
            deleteKeywords() {
                this.keyword = '';
            },
            follow(index, e) {
                if(e.target.innerHTML == 'follow') {
                    axios.post('/api/follow', {
                        user_id: this.user.id,
                        id: this.users[index].id,
                    })
                    .then((response) => {
                        e.target.innerHTML = 'unfollow';
                    })
                    .catch((error) => {
                        return
                    });
                } else {
                    axios.post('/api/unfollow', {
                        user_id: this.user.id,
                        id: this.users[index].id,
                    })
                    .then((response) => {
                        e.target.innerHTML = 'follow';
                    })
                    .catch((error) => {
                        return
                    });
                }
            },
            // music
            play() {
                this.$refs.tracks.play();
                this.isPlay = true
            },
            pause() {
                this.$refs.tracks.pause();
                this.isPlay = false
            },
            next() {
                var self = this
                self.$refs.tracks.pause();
                self.isLoadFile = true
                if(Object.keys(self.songs).length === 0) {
                    self.songs = this.$store.getters.getSongs
                }

                var rank = Object.keys(self.songs).find(key => self.songs[key].id === self.tracks.id);
                if(parseInt(rank) + 1 == self.songs.length) {
                    self.tracks = self.songs[0]
                } else {
                    self.tracks = self.songs.slice(parseInt(rank) + 1, parseInt(rank) + 2)[0];
                }
                self.$store.dispatch('addTracks', self.tracks)
                self.addAudioBase64AndPlay(self.tracks.song)
            },
            pre() {
                var self = this
                self.$refs.tracks.pause();
                self.isLoadFile = true
                if(Object.keys(self.songs).length === 0) {
                    self.songs = this.$store.getters.getSongs
                }
                
                var rank = Object.keys(self.songs).find(key => self.songs[key].id === self.tracks.id);
                if(parseInt(rank) != 0) {
                    self.tracks = self.songs.slice(parseInt(rank) - 1, parseInt(rank))[0];
                    self.$store.dispatch('addTracks', self.tracks)
                }
                self.addAudioBase64AndPlay(self.tracks.song)
            }
        }
    }
</script>
