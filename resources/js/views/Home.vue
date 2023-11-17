<script setup>
    import { ref } from 'vue'
</script>

<template>
    HOME
    
    MENU:
    <router-link :to="{name: 'signin'}">Go to login</router-link>
    <router-link :to="{name: 'signup'}">Go to register</router-link>
    
</template>