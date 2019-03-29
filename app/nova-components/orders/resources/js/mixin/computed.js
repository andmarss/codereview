export default {
    /**
     * Возвращает, выбрано ли "создание"
     * или "редактирование" заказа
     * @return {*}
     */
    isSelected(){
        return Nova.dealStore.getters.isSelected;
    },
    /**
     * Возвращает массив разрешенных transition'ов
     * @return {*}
     */
    transitions(){
        return Nova.dealStore.getters.transitions;
    },
    /**
     * Возвращает массив ошибок
     * @return {*}
     */
    errors(){
        return Nova.dealStore.getters.errors;
    },
    /**
     * Возвращает тип алерта
     */
    alertType(){
        return Nova.dealStore.getters.alertType;
    },
    /**
     * Возвращает текстовый контент алерта
     */
    alertText(){
        return Nova.dealStore.getters.alertText;
    }
}
