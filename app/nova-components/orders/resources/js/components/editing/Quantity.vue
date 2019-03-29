<template>
    <div class="flex bg-transparent">
        <div class="flex-shrink text-grey-darker text-center bg-grey-light hover:bg-grey px-2 py-2 rounded-l-lg pl-4 pr-4"
            @mousedown="startClumping('minus')" @mouseup="endClumping">
            -
        </div>
        <input type="text"
            class="flex-no-shrink outline-none bt-1 bb-1 border-grey-light text-grey-darkest text-center bg-white px-2 py-2" @input="input()" v-model="value" />
        <div class="flex-shrink text-grey-darker text-center bg-grey-light hover:bg-grey px-2 py-2 rounded-r-lg pl-4 pr-4"
            @mousedown="startClumping('plus')" @mouseup="endClumping">
            +
        </div>
    </div>
</template>

<script>
    import _ from 'lodash';

    export default {
        name: "Quantity",

        props: ['list', 'value'],

        data(){
            return {
                interval: null
            }
        },

        methods: {
            debounce: _.debounce(function () { this.$emit('selectList', this.list) }, 1000),
            /**
             * Обработчик события input
             */
            input(){
                let value = parseInt(this.value);

                if(value) {
                    if(value < 0){
                        this.value = -(value);
                    } else if (value === 0) {
                        this.value = 1;
                    }
                } else {
                    this.value = 1;
                }

                this.list.value = parseInt(this.value);

                this.debounce();
            },
            /**
             * Отлавливает начало нажатия
             * Если продолжительность нажатия больше 1 секунды -
             * - каждые 100мс инкрементирует или дикрементирует значение
             * @param command
             */
            startClumping(command){
                let start = Date.now();

                this.interval = setInterval(() => {

                    if((Date.now() - start) > 1000) {

                        if(command === 'plus') {
                            this.value = this.value + 1;
                        } else if (command === 'minus') {
                            this.value = this.value > 1 ? this.value - 1 : this.value;
                        }

                    }

                },100);

                if((Date.now() - start) < 1000) {
                    if(command === 'plus') {
                        this.value = this.value + 1;
                    } else if (command === 'minus') {
                        this.value = this.value > 1 ? this.value - 1 : this.value;
                    }
                }
            },
            /**
             * Отлавливает момент, когда пользователь отпустил левую кнопку мыши
             * После чего передает значение на сервер вызовом метода input()
             */
            endClumping(){
                clearInterval(this.interval);

                this.input()
            }
        }
    }
</script>
