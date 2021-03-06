
/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */
import store from './store';
require('./bootstrap');

window.Vue = require('vue');

/**
 * Next, we will create a fresh Vue application instance and attach it to
 * the page. Then, you may begin adding components to this application
 * or customize the JavaScript scaffolding to fit your unique needs.
 */

Vue.component('navbar', require('./components/Navbar.vue'));
Vue.component('urlform', require('./components/UrlForm.vue'));
Vue.component('footerpage', require('./components/FooterPage.vue'));
Vue.component('queryform', require('./components/QueryForm.vue'));
Vue.component('datatable', require('./components/DataTable.vue'));
Vue.component('urlqueryform', require('./components/UrlQuery.vue'));
Vue.component('tablequery', require('./components/TableQuery.vue'));

const app = new Vue({
    store,
    el: '#app'
});
