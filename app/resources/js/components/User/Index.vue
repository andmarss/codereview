<template>
    <div class="px-2 py-4 rounded bg-white shadow">
        <div class="py-3">
            <div class="flex justify-start items-center ml-auto mb-4">
                <div>
                    &#8592;&nbsp;&nbsp;<!-- <-\s\s -->
                    <a href="javascript:void(0)"
                       class="text-black underline hover:underline"
                       @click.prevent="goBack">
                        Список пользователей
                    </a>
                    &nbsp;&nbsp;/&nbsp;&nbsp;<!-- \s\s/\s\s -->
                </div>
                <h3 class="font-semibold">Пользователь #{{ id }}</h3>
            </div>
            <div class="px-2 py-4" v-if="email && name">
                <p class="font-semibold mb-3">Имя: {{name}}</p>
                <p class="font-semibold">Email: {{email}}</p>
            </div>
            <div class="px-2">
                <a :href="bd" class="text-primary dim font-bold no-underline">Войти под пользователем</a>
            </div>
        </div>
        <tabs class="px-2 py-4">
            <tab title="Заказы пользователя" :selected="true">
                <div v-if="deals.length > 0" class="py-4 px-2">
                    <div class="overflow-hidden overflow-x-auto relative">
                        <!--
                        tdStructure - порядок, по которому будут высраиваться td-элементы, так
                        что бы совпадало с titles
                        -->
                        <Table
                            :elements="deals"
                            :titles="['ID', 'Тип', 'Статус', 'Макеты']"
                            :fields="fields"
                            :tdStructure="['id', 'type', 'status']"
                        />
                    </div>

                    <!-- Пагинация -->
                    <Pagination
                        :elements="deals"
                        :currentPage="dealsPage"
                        :total="total"
                        :perPage="perPage"
                        @changePage="changeDealsPage"
                    />
                </div>
                <div v-if="deals.length === 0" class="py-4 px-2">
                    <h3 class="text-center font-semibold">Список заказов еще пуст</h3>
                </div>
            </tab>
            <tab title="Баланс пользователя">
                <div class="py-4 px-2">
                    <form class="flex flex-wrap w-full items-center" action="javascript:void(0);">
                        <p class="flex mr-6 font-semibold flex-grow-1">Баланс:</p>
                        <p class="flex mr-6 font-semibold flex-grow-1">{{balance}}&nbsp;&#8381;</p>
                        <input type="text" class="flex flex-grow-1 mr-6 bg-white p-2 bg-grey-lighter text-grey-darker border border-grey rounded leading-tight focus:outline-none focus:bg-white focus:border-grey"
                               v-model="sum"
                               id="payment-field"
                               placeholder="Введите сумму"
                               @input.prevent="input"
                               name="amount"
                        >
                        <input type="text" class="flex flex-grow-3 mr-6 bg-white p-2 bg-grey-lighter text-grey-darker border border-grey rounded leading-tight focus:outline-none focus:bg-white focus:border-grey"
                               v-model="comment"
                               id="comment-field"
                               placeholder="Комментарий"
                               @input.prevent="input"
                               name="amount"
                        >
                        <div class="flex flex-wrap flex-grow-1">
                            <div class="flex justify-center w-full mb-4">
                                <button class="bg-blue hover:bg-blue-dark font-bold text-white p-2 focus:outline-none rounded" @click.prevent="submit">Пополнить</button>
                            </div>
                            <div class="flex justify-center w-full">
                                <button class="bg-blue hover:bg-blue-dark font-bold text-white p-2 focus:outline-none rounded" @click.prevent="minus">Списать</button>
                            </div>
                        </div>
                    </form>
                    <div class="mt-4">
                        <!--
                        Блок будет показан, если количество оплат больше нуля
                        -->
                        <div v-if="payments.length > 0">
                            <div class="overflow-hidden overflow-x-auto relative">
                                <!--
                                tdStructure - порядок, по которому будут высраиваться td-элементы, так
                                что бы совпадало с titles
                                -->
                                <Table
                                    :elements="payments"
                                    :titles="['Дата', 'Сумма', 'Заказ', 'Описание']"
                                    :tdStructure="['created_at', 'sum', 'order', 'status']"
                                    :fields="paymentsFields"
                                />
                            </div>

                            <!-- Пагинация -->
                            <Pagination
                                :elements="payments"
                                :currentPage="paymentsPage"
                                :total="paymentsTotal"
                                :perPage="paymentsPerPage"
                                @changePage="changePaymentsPage"
                            />
                        </div>
                        <!--
                        Блок будет показан, если количество оплат равно нулю
                        -->
                        <div class="flex justify-center" v-if="payments.length === 0">
                            История оплат пользователя пуста
                        </div>
                    </div>
                </div>
            </tab>
        </tabs>
    </div>
</template>

<script>
    import Tabs from '../Tabs/Index';
    import Tab from '../Tabs/Tab';
    import Table from '../Table/Index';
    import Pagination from '../../../../nova-components/Userpayment/resources/js/components/Pagination';

    export default {
        props: ['id'],

        data(){
            return {
                csrf_token: document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                root: null,
                perPage: 10,
                deals: [],
                total: 0,
                fields: {},
                // пользователь
                name: '',
                email: '',
                balance: 0,
                bd: null,
                sum: '',
                comment: '',

                // оплаты
                payments: [],
                paymentsPerPage: 10,
                paymentsFields: {},
                paymentsTotal: 0,

                withdraw: false
            }
        },

        mounted(){
            this.root = window.location.origin;
            // обновляем информацию о пользователе
            this.updateUserPage();
            // обновляем таблицу сделок
            this.updateDealsPage(this.dealsPage);
            // обновляем таблицу оплат
            this.updatePaymentsPage(this.paymentsPage);
        },

        methods: {
            /**
             * Функция-обработчик изменения страницы таблицы
             * @param page
             */
            updateDealsPage(page){
                this.$http.post(`/admin/api/user/${this.id}/deals`, {
                    _token: this.csrf_token,
                    page,
                    per_page: this.perPage
                }).then(({data}) => {
                    this.deals = data.deals;

                    this.total = data.total;

                    this.fields = {};

                    if(this.deals.length > 0) {
                        this.deals = this.deals.map(deal => {
                            this.fields[deal.id] = {};

                            for(let prop in deal) {
                                switch (prop) {
                                    case 'id':
                                        this.fields[deal.id][prop] = {};

                                        this.fields[deal.id][prop]['value'] = deal.id;
                                        break;

                                    case 'type':
                                        this.fields[deal.id][prop] = {};

                                        this.fields[deal.id][prop]['value'] = deal.type;
                                        break;

                                    case 'status':
                                        this.fields[deal.id][prop] = {};

                                        this.fields[deal.id][prop]['value'] = deal.status;
                                        break;
                                }
                            }

                            deal['route'] = 'none';
                            deal['icon'] = 'download';
                            deal['title'] = 'Скачать';
                            deal['filesLoaded'] = deal.filesLoaded;
                            deal['url'] = deal.link;
                            deal['type'] = !!(deal.params.files && deal.params.files.link && deal.params.files.link.length > 0) ? 'url' : 'archive';

                            return deal;
                        })
                    }
                })
            },
            /**
             * Функция-обработчик изменения страницы таблицы платежей
             * @param page
             */
            updatePaymentsPage(page){
                this.$http.post(`/admin/api/user/${this.id}/payments`, {
                    _token: this.csrf_token,
                    page,
                    per_page: this.paymentsPerPage
                }).then(({data}) => {
                    this.payments = data.payments;

                    this.paymentsTotal = data.total;

                    this.paymentsFields = {};

                    if(this.payments.length > 0) {
                        this.payments.forEach(payment => {
                            this.paymentsFields[payment.id] = {};

                            for(let key in payment){
                                switch (key) {
                                    case 'created_at':
                                        this.paymentsFields[payment.id][key] = {};

                                        this.paymentsFields[payment.id][key]['value'] = payment.date;

                                        break;
                                    case 'sum':

                                        this.paymentsFields[payment.id][key] = {};

                                        this.paymentsFields[payment.id][key]['value'] = payment['type'] === 1
                                            ? `<span class="text-green-dark">+${payment.sum} &#8381;</span>`
                                            : `<span class="text-red-dark">${payment.sum} &#8381;</span>`;

                                        this.paymentsFields[payment.id][key]['asHtml'] = true;
                                        break;
                                    case 'order':
                                        this.paymentsFields[payment.id][key] = {};

                                        this.paymentsFields[payment.id][key]['value'] = payment[key];
                                        break;
                                    case 'status':
                                        this.paymentsFields[payment.id][key] = {};

                                        this.paymentsFields[payment.id][key]['value'] = payment.description;
                                        break;
                                }
                                continue;
                            }
                        })
                    }
                })
            },
            /**
             * Обновляет информацию о пользователе
             * */
            updateUserPage(){
                this.$http.get(`/admin/api/user/${this.id}`).then(({data}) => {
                    this.name = data.name;
                    this.email = data.email;
                    this.balance = data.balance;
                    this.bd = data.bd;
                })
            },
            /**
             * Изменяет номер страницы в таблице
             * обрабатывает клик в пагинации
             * */
            changeDealsPage(page) {
                this.$store.dispatch('updateDealsPage', page);

                this.updateDealsPage(page);
            },
            /**
             * Изменяет номер страницы в таблице оплат
             * обрабатывает клик в пагинации
             * */
            changePaymentsPage(page) {
                this.$store.dispatch('updatePaymentsPage', page);

                this.updatePaymentsPage(page);
            },
            /**
             * Возвращает на главную страницу админки
             * */
            goBack(){
                this.$store.dispatch('updateDealsPage', 1);

                this.$router.push({name: 'admin'})
            },
            /**
             * функция-обработчик запроса оплаты
             * */
            submit(){
                /**
                 * Если поле пустое - не даем отправить запрос
                 */
                if(!this.sum) {
                    toastr.error('Заполните поле для пополнения баланса');

                    return;
                }
                /**
                 * Если значение меньше нуля - не даем отправить запрос
                 * */
                if(this.sum && parseFloat(this.sum) <= 0){
                    toastr.error('Значение должно быть больше нуля');

                    return;
                }

                if(!this.comment.trim().length) {
                    toastr.error('Заполните поле комментария');

                    return;
                }

                this.$http.post(`/admin/api/pay`, {
                    _token: this.csrf_token,
                    user_id: this.id,
                    minus: this.withdraw,
                    comment: this.comment,
                    amount: this.sum
                }).then(({data}) => {
                    if(data && data.access) {

                        if(data.error && data.error.confirmation) {
                            toastr.error('При выполнении запроса произошла ошибка. Повторите попытку, либо сообщите о проблеме разработчикам');

                            return;
                        }

                        if(data.error && data.error.user) {
                            toastr.error('Пользователь не найден');

                            return;
                        }

                        toastr.success(`Баланс пользователя успешно пополнен на сумму ${this.sum} руб.`);

                        this.balance = data.balance;
                        // очищаем поля
                        this.sum = '';
                        this.comment = '';
                        // если это было списание - возвращаем к значению по умолчанию
                        if(this.withdraw) {
                            this.withdraw = false;
                        }
                        // обновляем таблицу оплат
                        this.updatePaymentsPage(this.paymentsPage);
                    }
                });

                return;
            },
            /**
             * Обработчик списания со счета
             * */
            minus(){
                this.withdraw = true;

                this.submit();
            },
            /**
             * Функция-обработчик ввода текста в поле
             * */
            input(e){
                e.preventDefault();

                let number = parseFloat(this.sum);
                /**
                 * Если пользователь вводит не число - не даем ему сделать это
                 */
                if(isNaN(number)) {
                    this.sum = '';
                }
            }
        },

        computed: {
            /**
             * Возвращает номер страницы для таблицы сделок
             * @return {default.computed.dealsPage|(function())|number|default.getters.dealsPage|dealsPage}
             */
            dealsPage(){
                return this.$store.getters.dealsPage;
            },
            /**
             * Возвращает номер страницы для таблицы оплат
             * @return {default.computed.paymentsPage|(function())|default.getters.paymentsPage|number}
             */
            paymentsPage(){
                return this.$store.getters.paymentsPage;
            }
        },

        components: {
            tab: Tab,
            tabs: Tabs,
            Table,
            Pagination
        }
    }
</script>

<style scoped lang="scss">
    .flex-grow-1 {
        flex-grow: 1;
    }

    .flex-grow-3 {
        flex-grow: 3;
    }
</style>
