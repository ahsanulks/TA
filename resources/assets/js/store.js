import Vue from 'vue';
import Vuex from 'vuex';

Vue.use(Vuex);

const state = {
  lastUpdate: ''
}
const getters = {
}
const mutations = {
  lastUpdated(state, value) {
    state.lastUpdate = value;
  }
}
const actions = {
    
}
export default new Vuex.Store({
    state,
    getters,
    mutations,
    actions
});