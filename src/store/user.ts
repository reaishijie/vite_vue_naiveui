import { defineStore } from "pinia"
import { ref } from "vue"
import { type IUser } from "../types"

export const useUserStore = defineStore('user',()=>{
    //userInfo 是符合IUser接口的一个对象
    const userInfo = ref<IUser | null>(null) 
    const token = ref('')
    const logState = ref(false)

    function setUserInfo(userDate:IUser) {
        userInfo.value = userDate
        logState.value = true
    }

    function setToken(newToken: string){
        token.value = newToken
    }

    function logout() {
        userInfo.value = null
        token.value = ''
        logState.value = false
    }

    return {
        userInfo,
        setUserInfo,
        logState,
        token,
        setToken,
        logout
    }
},
{persist:true}
)