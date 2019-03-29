<template>
    <div>
        <heading :level="2" class="flex mb-3 font-semibold">{{ header }}</heading>

        <div class="flex flex-wrap -mx-2 justify-start mb-3 items-stretch">
            <div class="flex lg:w-1/3 md:w-1/2 sm:w-full px-2 md:mb-3 sm:mb-3" v-for="type in types">
                <div class="shadow-lg rounded overflow-hidden new-order-card">
                    <div class="w-full relative flex items-center justify-center bg-grey" style="min-height: 6rem">
                        <img class="w-full h-auto" :src="`/img/printphotobook/${type}.jpg`" :alt="type" />
                    </div>
                    <div class="px-6 py-4">
                        <div class="font-bold mb-2 w-full text-lg items-center">{{ type }}</div>
                        <div class="w-full text-sm"><span class="font-bold">Размеры: </span>{{Array.isArray(typesInfo[type].formats) ? typesInfo[type].formats.join(', ') : typesInfo[type].formats}}</div>
                    </div>
                    <div class="px-6 py-4 flex justify-center new-order-button-wrapper"
                         ref="newOrderButtonWrapper"
                    >
                        <a href="javascript:void(0);" class="bg-blue hover:bg-blue-dark text-white font-bold py-2 px-4 rounded-full no-underline" @click.prevent="createAlbum({type, category})">
                            <span v-if="load === false">Создать</span>
                            <img :src="`${root}/img/loader.gif`" class="w-2" alt="" v-else="load">
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import axios from 'axios';

    export default {
        name: "NewOrder",

        props: [
            'header',
            'types',
            'category',
            'typesInfo'
        ],

        data(){
            return {
                load: false,
                root: window.location.origin
            }
        },

        methods: {
            createAlbum(album){
                this.load = true;

                axios.post('/printphotobook', {
                    type: album.type,
                    category: album.category,
                    _token: document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }).then(({data}) => {
                    Nova.dealStore.dispatch('changeSelected', true);
                    this.load = false;

                    this.$router.push({name: 'editing', params: {id: data.id}});
                });
            },
            /**
             * Устанавливает отступ сверху для блока кнопки так,
             * что бы он прижимался к нижнему краю карточки
             * @param element
             */
            marginTop(element){
                // родительский div
                let parent = element.parentNode;
                // высота родителя
                let parentHeight = parent.offsetHeight;
                // высота элемента
                let elementHeight = element.offsetHeight;
                // отступ от верхнего края родителя до элемента
                let elementOffset = element.offsetTop;
                // считаем, на сколько элемент должен отступить так
                // что бы прижаться к нижней границе родителя
                let toBottom = parentHeight - (elementHeight + elementOffset);

                element.style.marginTop = `${toBottom}px`;
            }
        },

        mounted(){
            this.$refs.newOrderButtonWrapper.forEach(element => {
                this.marginTop(element);
            });
        }
    }
</script>

<style scoped lang="scss">
    .new-order-card {
        position: relative;
    }
</style>
