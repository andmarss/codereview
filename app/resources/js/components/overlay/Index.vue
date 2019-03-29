<template>
    <div class="overlay" v-if="show">
        <div class="overlay-content">
            <div class="loader-wrapper"><loader width="30"></loader></div>
            <div v-if="content">
                <p class="overlay-text">{{content}}<span v-if="showDots">{{txt}}</span></p>
                <p v-if="showProgress">{{progressContent}}</p>
            </div>
            <p v-else class="overlay-text">Подождите{{txt}}</p>
        </div>
    </div>
</template>

<script>
    export default {
        name: "Index",

        data(){
            return {
                period: 500,
                txt: '...',
                isDeleting: false,
                timeout: null
            }
        },

        methods: {
            tick(){
                if(this.txt.length === 3) {
                    this.txt = '';
                } else {
                    this.txt += '.';
                }

                if(this.timeout){
                    clearTimeout(this.timeout);
                }

                if(this.showDots) {
                    this.timeout = setTimeout(() => this.tick(), this.period);
                }
            }
        },

        mounted(){
            if(this.showDots) {
                this.tick();
            }
        },

        computed: {
            show(){
                return custom.getters.showOverlay;
            },

            content(){
                return custom.getters.overlayContent;
            },

            showDots(){
                return custom.getters.showDots;
            },

            showProgress(){
                return custom.getters.showProgress;
            },

            progressContent(){
                return custom.getters.progressContent;
            }
        }
    }
</script>

<style scoped lang="scss">
    .overlay {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        z-index: 1000 !important;
        background-color: rgba(0,0,0,.8);
        color: #fff;

        .overlay-content {
            margin: 0;
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            text-align: center;
        }

        .loader-wrapper {
            margin-bottom: 1.5rem;
        }

        .overlay-text {
            font-size: 1.5rem;
        }
    }
</style>
