<template>
    <div class="fixed pin-r pin-b w-full" v-if="hasButtons()">
        <div class="flex flex-wrapper bg-white px-2 py-4 items-center">
            <div class="w-1/3">
                <p v-if="sum !== undefined && discount === 0">
                    <span>Сумма заказа: {{sum}} руб.</span>
                </p>
                <p v-else-if="discount > 0">
                    <span>Сумма заказа: {{sumWithDiscount}} руб.</span>
                </p>
                <a href="javascript:void(0);"
                   class="text-grey"
                   v-if="deleteButton"
                   @click.prevent="open">{{deleteButton.title}}</a>
            </div>
            <div class="w-2/3 flex space-between">
                <div class="flex justify-start" :class="forwardButton && backwardButton ? 'w-1/2' : 'w-full'" v-if="backwardButton">
                    <a href="javascript:void(0)"
                       class="flex items-center bg-white text-grey-darkest font-semibold py-2 px-4 border border-grey-light rounded-full no-underline text-center"
                       v-bind:class="canGo(backwardButton.transition, {ignore: backwardButton.ignore}) ?
                         'hover:bg-grey-lightest'
                         : 'cursor-not-allowed opacity-50'"
                       @click.prevent="canGo(backwardButton.transition, {ignore: backwardButton.ignore}) ?
                         goTransition(backwardButton.transition, {alert: backwardButton.alert}) :
                          false">{{backwardButton.title}}</a>
                </div>
                <div class="flex justify-end" :class="forwardButton && backwardButton ? 'w-1/2' : 'w-full'" v-if="forwardButton">
                    <a href="javascript:void(0)"
                       class="flex items-center bg-blue text-white font-bold py-2 px-2 rounded-full no-underline"
                       v-bind:class="canGo(forwardButton.transition, {ignore: forwardButton.ignore}) ?
                        'hover:bg-blue-dark' :
                         'cursor-not-allowed opacity-50'"
                       @click.prevent="canGo(forwardButton.transition, {ignore: forwardButton.ignore}) ?
                         goTransition(forwardButton.transition, {alert: forwardButton.alert}) :
                          false">{{forwardButton.title}}</a>
                </div>
            </div>
        </div>
        <transition name="fade">
            <Modal
                ref="modal"
                :title="'Удаление заказа'"
                :question="'Вы действительно хотите удалить заказ?'"
                :working="working"
                :active="showModal"
                :cancelButtonText="'Нет'"
                :confirmButtonText="'Удалить заказ'"
                @confirm="confirm"
                @close="close"
            />
        </transition>
    </div>
</template>

<script>
    import Modal from '../modal/Index';
    import methods from '../../mixin/methods';
    import computed from '../../mixin/computed';

    export default {
        name: "Index",

        props: {
            sum: {
                type: Number,
                default: 0,
                required: true
            },
            discount: {
                type: Number,
                default: 0,
                required: false
            },
            sumWithDiscount: {
                type: Number,
                default: 0,
                required: false
            },

            working: false
        },

        data(){
            return {
                showModal: false,
                id: this.$route.params.id
            }
        },

        methods: {
            close(){
                this.showModal = false;
            },

            open(){
                this.showModal = true;
            },

            confirm(){
                this.$emit('delete');
            },

            hasButtons(){
                return this.forwardButton !== null || this.backwardButton !== null || this.deleteButton !== null;
            },

            ...methods
        },

        computed: {
            // Кнопки
            deleteButton(){
                return Nova.dealStore.getters.deleteButton;
            },

            forwardButton(){
                return Nova.dealStore.getters.forwardButton;
            },

            backwardButton(){
                return Nova.dealStore.getters.backwardButton;
            },

            ...computed
        },

        components: {
            Modal
        }
    }
</script>
