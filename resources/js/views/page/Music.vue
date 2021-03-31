<template>
    <div class="h-100 box-music">
        <div class="d-flex justify-content-between box-menu p-3 pt-4">
            <div class="d-flex action">
                <div class="d-flex align-items-center" v-on:click="trends()"><i class="fas fa-analytics"></i><span>trends</span></div>
                <div class="d-flex align-items-center" v-on:click="playlist()"><i class="fas fa-th-list"></i><span>playlist</span></div>
                <div class="d-flex align-items-center" v-on:click="your()"><i class="fas fa-compact-disc"></i><span>your</span></div>
                <div class="d-flex align-items-center" v-on:click="up()"><i class="fas fa-upload"></i><span>up</span></div>
            </div>
            <div class="d-flex align-items-center py-1 px-4 search">
                <div class="d-flex align-items-center dropdown w-100">
                    <input type="text" placeholder="tracks, artists, playlist,..." v-model="keyword" class="w-100" data-toggle="dropdown">
                    <!-- <div class="item loading-3" v-if="loading">
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
                    </div> -->
                </div>
            </div>
        </div>
        <div class="overflow-scroll vh-100 box-main">
            <!-- <router-view v-if="!active && !isUp"></router-view> -->
            <div v-if="active" class="mb-5">
                <div class="d-flex justify-content-between align-self-center shadow-sm mb-2 tracks" v-for="(song, index) in songs" :key="index">
                    <div @click="openSong(index)">
                        <p class="m-0">{{song.title}}</p>
                        <p class="m-0 artists_names">{{song.artists}}</p>
                    </div>
                    <div class="d-flex align-self-center">
                        <i class="fas fa-heart"></i>
                        <div class="dropdown" v-if="song.authors == user.id">
                            <i class="fas fa-ellipsis-h ml-2" data-toggle="dropdown"></i>
                            <div class="dropdown-menu dropdown-menu-right mt-1 px-2 shadow">
                                <i @click="deleteTracks(index)" class="fas fa-trash-alt dropdown-item"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <form v-else @submit.prevent="uploadSong" class="form-post p-2" enctype="multipart/form-data">
                <!-- <div v-if="image != ''" class="bg-images my-2" v-bind:style="{backgroundImage: `url('` + image + `')`}" style="max-height:250px;"></div> -->
                <div class="d-flex align-items-center"><i class="fas fa-upload"></i><span>up songs your.</span></div>
                <div class="box-input">
                    <input type="text" placeholder="song name." v-model="title">
                </div>
                <div class="box-input">
                    <input type="text" placeholder="artists." v-model="artists">
                </div>
                <div class="box-input d-flex">
                    <div class="image-upload">
                        <label for="file-input" class="m-0"><p>file</p></label>
                        <input id="file-input" v-on:change="convertBase64" type="file"/>
                    </div>
                    <div v-if="filename != ''" class="mx-3">
                        <p>{{filename}}</p>
                    </div>
                </div>
                <p v-if="message != ''" class="message">{{message}}</p>
                <div class="d-flex align-items-center shadow-sm btn-submit">
                    <input type="submit" value="up" class="mx-auto">
                    <div v-if="spinner" class="spinner-grow spinner-grow-sm"></div>
                </div>
            </form>
        </div>
    </div>

</template>
<script>
    export default {
        data() {
            return {
                user:this.$store.getters.getUser,
                song: '',
                artists: '',
                title: '',
                filename: '',
                songs: {},
                message: '',
                keyword: '',
                active: true,
                spinner: false,
            }
        },
        mounted() {
            this.axios
            .get('/api/music/index')
            .then(response => {
                this.songs = response.data
                this.$store.dispatch('addSongs', response.data)
            })
            .catch(response => {
                return
            });
        },
        methods: {
            convertBase64(e){
                if (e.target.files[0]) {
                    let data = this
                    if (e.target.files[0].type.slice(0, 6) === 'audio/') {
                        var reader = new FileReader();
                        reader.onload = function (e) {
                            e.preventDefault();
                            e.stopPropagation();
                            data.song = e.target.result;
                        }
                        data.filename = e.target.files[0].name;
                        reader.readAsDataURL(e.target.files[0]);
                    }
                }
            },
            uploadSong(e) {
                if(this.title == "" || this.artists == "" || this.song == "") {
                    this.message = 'incomplete data. Please update for more information!'
                    return
                } else {
                    this.message = ""
                }
                e.preventDefault();
                if(!this.$store.getters.loggedIn) {
                    this.$router.push({ name: 'login' })
                }
                this.spinner = true
                axios.post('/api/music/store', {
                    authors: this.user.id,
                    song: this.song,
                    title: this.title,
                    artists: this.artists
                })
                .then((response) => {
                    this.spinner = false
                    this.songs.push(response.data)
                    this.song = ''
                    this.artists = ''
                    this.title = ''
                    this.message = 'Up successfully!'
                    this.filename = ''
                    this.$store.dispatch('addSongs', this.songs)
                    return
                })
                .catch((error) => {
                    if(error.response) {
                        this.message = error.response.data.message
                        return
                    } else {
                        return
                    }
                });
            },
            up() {
                this.active = false;
                this.message = ''
            },
            trends() {
                this.active = true;
                this.message = ''
            },
            openSong(index) {
                this.$store.dispatch('addTracks', this.songs[index])
            },
            deleteTracks(index) {
                axios
                .post('/api/music/destroy', {
                    id: this.songs[index].id
                })
                .then((response) => {
                    this.songs = this.songs.filter(item => item.id !== this.songs[index].id);
                    this.$store.dispatch('addSongs', this.songs)
                    if(this.$store.getters.getTracks.id == this.songs[index].id) {
                        this.$store.dispatch('addTracks', '')
                    }
                    return
                    })
                .catch((error) => {
                    return
                });

            },
        }
    }
</script>