import { createStore } from 'vuex'
import createPersistedState from "vuex-persistedstate";

const store = createStore({
    state () {
        return {
            userId: null
        }
    },
    mutations: {
        setUserId (state, id) {
            state.userId = id
        }
    },
    plugins: [createPersistedState()],
})

export default store