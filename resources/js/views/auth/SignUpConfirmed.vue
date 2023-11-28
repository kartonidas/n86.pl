<script>
    import { ref } from 'vue'
</script>

<template>
    <div class="text-center">
        {{ $t('register.registering_confirmed') }}
        
        <div class="mt-4">
            <router-link :to="{name: 'signin'}">
                {{ $t('register.signin') }}
            </router-link>
        </div>
    </div>
</template>