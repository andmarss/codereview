<template>
    <tr v-if="element">
        <td class="w-8"></td>

        <!-- Fields -->
        <td v-for="prop in structure" class="text-center">
            <!--Если содержимео field нужно вывести как html - выводим-->
            <span v-if="fields[prop.toLowerCase()].asHtml" v-html="fields[prop.toLowerCase()].value"></span>
            <!--
            Если field.value - объект всего с двумя свойствами: id и url, значит это заказ
            url - ссылка, на которую пользователь должен перейти при клике
            При клике вызывается goTo, которая ожидает объект с двумя свойствами: route и id
            route получаем из функции getRoute, которая с помощью регулярки возвращает имя роута,
            куда нужно перенаправить пользователя
            id получаем из field.value.id
            -->
            <span v-if="getType(fields[prop].value) === 'object'
            && (fields[prop].value.url)
            && (fields[prop].value.id)">
                <a href="javascript:void(0);"
                   class="btn btn-link text-primary dim"
                   @click.prevent="goTo({
                    route: getRoute( fields[prop].value.url ),
                    id: fields[prop].value.id
                 })">
                    Заказ №{{fields[prop].value.id}}
                </a>
            </span>
            <!--Иначе просто отображаем значение field, как строковое-->
            <span v-if="(getType(fields[prop].value) !== 'object')
            &&
            (!fields[prop.toLowerCase()].asHtml)"
                  class="font-semibold">
                {{fields[prop.toLowerCase()].value}}
            </span>
        </td>

        <td class="td-fit text-center pr-6" v-if="element.route">
            <!-- View Resource Link -->
            <span v-if="element.route !== 'none'">
                <router-link
                    class="cursor-pointer text-70 hover:text-primary mr-3"
                    :to="{ name: element.route, params: {
                        id: element.id
                    }}"
                    :title="element.title"
                >
                    <icon :type="element.icon" width="22" height="18" view-box="0 0 22 16" />
                </router-link>
            </span>
            <span v-if="element.route === 'none' && element.filesLoaded && element.url">
                <a class="text-blue hover:text-blue-dark font-semibold no-underline hover:no-underline"
                   :href="element.url" v-if="element.type === 'archive'">
                    <i class="fas fa-download"></i>
                </a>
                <a class="text-blue hover:text-blue-dark font-semibold no-underline hover:no-underline"
                    :href="element.url" target="_blank" v-if="element.type === 'url'">
                    <i class="fas fa-link"></i>
                </a>
            </span>
            <span v-if="element.route === 'none' && (!element.filesLoaded || !element.url)">
                <a class="text-black no-underline hover:no-underline"
                   href="javascript:void(0);">
                    -
                </a>
            </span>
        </td>
    </tr>
</template>

<script>
    import Icon from '../Icon';
    import methods from '../../mixin/methods';

    export default {
        name: "TableRow",

        mixins: [methods],

        data(){
            return {
                root: window.location.origin
            }
        },

        props: [
            'element',
            'fields',
            'structure'
        ],

        methods: {
            open(){
                this.$emit('openModal', this.element.id);
            }
        },

        components: {
            icon: Icon
        }
    }
</script>
