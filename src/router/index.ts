import { createRouter, createWebHistory } from 'vue-router'
const routes = [
    {
        name:'home',
        path : "/home",
        component: () => import ('../views/Home.vue')
    },
    {
        name:'register',
        path : "/register",
        component: () => import ('../views/Register.vue')
    },
    {
        name:'login',
        path : "/login",
        alias:['/index','/'],
        component: () => import ('../views/Login.vue')
    },
]

const router = createRouter({
    history: createWebHistory(),
    routes
})

export default router