<template>
    <div>
        <heading class="mb-6">Новые регистрации</heading>

        <card class="px-3">
            <div>
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
                        :emptyMessage="searchField.length > 0
                        ? `Пользователи с указанным E-mail адресом не найдены`
                        : `Список пользователей пуст`"
                        @search="search"
                    />
                </div>

                <Pagination
                    :elements="users"
                    :currentPage="usersPage"
                    :total="total"
                    :perPage="perPage"
                    @changePage="changePage"
                />
            </div>
        </card>
    </div>
</template>

<script>
    import axios from 'axios';
    import methods from '../mixin/methods';

export default {
    mixins: [methods],

    data(){
        return {
            csrf_token: document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            users: [],
            fields: {},
            total: 0,
            perPage: 10,
            searchField: ''
        };
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

            axios.post('/api/users', {
                page,
                email,
                per_page: this.perPage,
                _token: this.csrf_token
            }).then(({data}) => {
                if(data.access) {
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
                } else {
                    this.goDashboard();
                }
            });
        },
        /**
         * обработчик изменения страницы для пагинации
         * @param page
         */
        changePage(page){
            Nova.usersStore.dispatch('updateUsersPage', page);

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
            Nova.usersStore.dispatch('updateUsersPage', 1).then(() => {
                this.updatePage(this.usersPage);
            });
        }
    },

    computed: {
        /**
         * Возвращает текущий номер страницы таблицы
         * @return {default.computed.usersPage|(function())|default.getters.usersPage|number}
         */
        usersPage(){
            return Nova.usersStore.getters.usersPage;
        }
    }
}
</script>

<style>
/* Scoped Styles */
</style>
