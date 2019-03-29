<template>
    <div class="px-2 py-4 rounded bg-white shadow">
        <!--
        Блок будет показан, если количество оплат больше нуля
        -->
        <div v-if="users.length > 0">
            <div class="overflow-hidden overflow-x-auto relative">
                <!--
                tdStructure - порядок, по которому будут высраиваться td-элементы, так
                что бы совпадало с titles
                -->
                <Table
                    :elements="users"
                    :titles="['ID', 'Имя', 'Email']"
                    :fields="fields"
                    :tdStructure="['id', 'name', 'email']"
                    :useSearch="true"
                    @search="search"
                />
            </div>

            <!-- Пагинация -->
            <Pagination
                :elements="users"
                :currentPage="usersPage"
                :total="total"
                :perPage="perPage"
                @changePage="changePage"
            />
        </div>
        <!--
        Блок будет показан, если количество оплат равно нулю
        -->
        <div class="flex justify-center" v-if="users.length === 0">
            Зарегестрированных пользователей еще нет
        </div>
    </div>
</template>

<script>
    import Pagination from '../../../../nova-components/Userpayment/resources/js/components/Pagination';
    import Table from '../Table/Index';

    export default {
        name: "Index.vue",

        data(){
            return {
                csrf_token: document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                users: [],
                fields: {},
                total: 0,
                perPage: 10,
                searchField: ''
            }
        },

        mounted(){
            this.updatePage(this.usersPage);
        },

        methods: {
            /**
             * Функция-обработчик изменения страницы таблицы
             * @param page
             */
            updatePage(page){
                // если поле поиска пустое - просто обнавляем список пользователей
                // иначе фильтруем по email
                let email = this.searchField.trim().length > 0 ? this.searchField : null;

                this.$http.post('/admin/api/users', {
                    page,
                    email,
                    per_page: this.perPage,
                    _token: this.csrf_token
                }).then(({data}) => {
                    this.fields = {};
                    this.users = data.users;
                    this.total = data.total;

                    if(this.users.length > 0) {
                        this.users = this.users.map(user => {
                            this.fields[user.id] = {};

                            for(let prop in user) {
                                switch (prop) {
                                    case 'id':
                                        this.fields[user.id][prop] = {};

                                        this.fields[user.id][prop]['value'] = user.id;
                                        break;

                                    case 'name':
                                        this.fields[user.id][prop] = {};

                                        this.fields[user.id][prop]['value'] = user.name;
                                        break;

                                    case 'email':
                                        this.fields[user.id][prop] = {};

                                        this.fields[user.id][prop]['value'] = user.email;
                                        break;
                                }
                            }

                            user['route'] = 'view-user';
                            user['icon'] = 'view';
                            user['title'] = 'Подробнее';

                            return user;
                        });
                    }
                });
            },
            /**
             * обработчик изменения страницы для пагинации
             * @param page
             */
            changePage(page){
                this.$store.dispatch('updateUsersPage', page);

                this.updatePage(page);
            },
            /**
             * Обработчик изменения поля поиска
             * @param {string} email
             * */
            search(email){
                this.searchField = email;
                // каждый раз, когда поле изменяется
                // страница таблицы сбрасывается на первую страницу
                this.$store.dispatch('updateUsersPage', 1).then(() => {
                    this.updatePage(this.usersPage);
                });
            }
        },

        computed: {
            /**
             * Возвращает номер страницы для таблицы
             * @return {default.computed.usersPage|(function())|number|default.getters.usersPage|usersPage}
             */
            usersPage(){
                return this.$store.getters.usersPage;
            }
        },

        components: {
            Table,
            Pagination
        }
    }
</script>
