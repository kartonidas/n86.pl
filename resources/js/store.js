import { createStore } from 'vuex'
import createPersistedState from "vuex-persistedstate";

const store = createStore({
    state () {
        return {
            userId: null,
            toastMessage: null,
            permissions: null,
        }
    },
    mutations: {
        setUserId (state, id) {
            state.userId = id
        },
        
        setUserPermission (state, permissions) {
            state.permissions = permissions
        },
        
        setToastMessage (state, data) {
            state.toastMessage = data
        },
    },
    getters: {
        toastMessage (state) {
            return state.toastMessage
        }
    },
    plugins: [createPersistedState()],
})

export default store