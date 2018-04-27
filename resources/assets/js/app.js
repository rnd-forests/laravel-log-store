import Vue from 'vue';
import ElementUI from 'element-ui';
import 'element-ui/lib/theme-chalk/index.css';
import en from 'element-ui/lib/locale/lang/en'
import ElementUILocale from 'element-ui/lib/locale'

/**
 * Configure Element UI library.
 */
Vue.use(ElementUI);
ElementUILocale.use(en);

/**
 * Register global Vue components.
 */
Vue.component('practice-log', require('./components/PracticeLog.vue'));

/**
 * Create new Vue instance.
 */
const app = new Vue({
    el: '#app'
});
