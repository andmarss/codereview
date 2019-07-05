##### Навигация по проекту в Laravel Nova

Панель 
> /nova-components/Maincard

Новые регистрации (для модератора) 
> /nova-components/Userslist

Изменение информации 
> nova-components/Userinformation

Личные данные
> nova-components/Userprofile

Для сборки *.vue файлов для соответствующих страниц нужно зайти в соответствующую странице папку 
(например, для "Изменение информации" нужно зайти в директорию "nova-components/Userinformation"), 
и вызвать одну из нижеперечисленных команд:

#####npm-команды
> ######npm run watch
> запускает наблюдение за всеми изменениями внутри *.js, *.vue, *.scss файлов

> ######npm run prod
> собирает все *.js, *.vue, файлы в один минифицированный dist/js/card.js|tool.js файл,
 все *.scss файлы в один минифицированный dist/css/card.css|tool.css
 
 Отображение Nova Tool'ов и Nova Card'ов регулируется в файле NovaServiceProvider, в методе tools()
 
 > ######app/Providers/NovaServiceProvider.php:68
