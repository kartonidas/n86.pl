import { defineStore } from 'pinia'

export const appStore = defineStore('store', {
    state: () => {
        return {
            userId: null,
            isOwner: false,
            toastMessage: null,
            permissions: null,
            firebase: null,
            tableOrder: {},
            tableFilter: {},
            error404: null,
        };
    },
    actions: {
        setUserId (id) {
            this.userId = id;
        },
        
        setIsOwner (state) {
            this.isOwner = state;
        },
        
        setUserPermission (permissions) {
            this.permissions = permissions;
        },
        
        setToastMessage (data) {
            this.toastMessage = data;
        },
        
        setError404 (data) {
            this.error404 = data;
        },
        
        setTableOrder (table, column, order) {
            if (this.tableOrder[table] == undefined)
                this.tableOrder[table] = {};
            
            this.tableOrder[table] = {
                'col' : column,
                'dir' : order,
            };
        },
        
        getTableOrder(table) {
            return this.tableOrder[table];
        },
        
        setTableFilter (table, filter) {
            if (this.tableFilter[table] == undefined)
                this.tableFilter[table] = {};
            
            this.tableFilter[table] = filter;
        },
        
        setFirebase(firebase) {
            this.firebase = firebase
        },
        
        getTableFilter(table) {
            return this.tableFilter[table];
        },
    },
    persist: true,
})
