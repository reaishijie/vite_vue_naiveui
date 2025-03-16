<script setup lang="ts" name="Test">
import { reactive } from 'vue'
import { type IUser } from '../types';
const list = reactive<IUser>({
    username:"",
    password:"",
    email:""
})
</script>

<template>
  <n-input type="text" :v-model="list.username" @keydown="()=>console.log(list)" />
  <n-space>
  <n-input type="text" v-model:value="list.username" @keydown="()=>console.log(list)" />
  </n-space>



</template>

<style scoped>
  
</style>