<template>
    <div class="w-full">
        <!-- Изображение -->
        <img class="image"
             :src="src"
             :alt="alt"
             style="width:100%;max-width:300px"
             @click.prevent="openModal"
        >
        <!-- модалка -->
        <div ref="modal"
             class="image-modal"
             v-if="show"
             @click.prevent="triggerOverlay">

            <!-- кнопка "закрыть" -->
            <span class="close" @click.prevent="closeModal">&times;</span>

            <!-- Изображение в модалке -->
            <img class="modal-content" ref="image" :src="src">

            <!-- текст изображения -->
            <div class="caption">{{alt}}</div>
        </div>
    </div>
</template>

<script>
    export default {
        props: {
            src: {
                type: String,
                required: true
            },
            alt: {
                type: String,
                required: false,
                default: 'Изображение'
            }
        },

        data(){
            return {
                show: false
            }
        },

        methods: {
            openModal(){
                this.show = true;
            },

            closeModal(){
                this.show = false;
            },

            triggerOverlay(e){
                let target = e.target;

                if(target.classList.contains('image-modal') && this.show) {
                    this.show = false;
                }

                return;
            }
        }
    }
</script>

<style scoped lang="scss">
    .image {
        border-radius: 5px;
        cursor: pointer;
        transition: 0.3s;
    }

    .image:hover {
        opacity: .7;
    }

    .image-modal {
        position: fixed;
        z-index: 20;
        padding-top: 100px;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        overflow: auto;
        background-color: rgb(0,0,0);
        background-color: rgba(0,0,0,0.9);
    }

    .modal-content {
        margin: auto;
        display: block;
        width: 80%;
        max-width: 700px;
    }

    .caption {
        margin: auto;
        display: block;
        width: 80%;
        max-width: 700px;
        text-align: center;
        color: #ccc;
        padding: 10px 0;
        height: 150px;
    }

    .modal-content, .caption {
        animation-name: zoom;
        animation-duration: 0.6s;
    }

    @keyframes zoom {
        from {transform:scale(0)}
        to {transform:scale(1)}
    }

    .close {
        position: absolute;
        top: 15px;
        right: 35px;
        color: #f1f1f1;
        font-size: 40px;
        font-weight: bold;
        transition: 0.3s;
    }

    .close:hover,
    .close:focus {
        color: #bbb;
        text-decoration: none;
        cursor: pointer;
    }

    @media only screen and (max-width: 700px){
        .modal-content {
            width: 100%;
        }
    }
</style>
