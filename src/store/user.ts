import { defineStore } from "pinia"
import { ref } from "vue"
import { type IStoreUser } from "../types"

export const useUserStore = defineStore('user',()=>{
    //storeUserInfo 是符合IStoreUser接口的一个对象
    const storeUserInfo = ref<IStoreUser | null>(null) 
    const token = ref('')
    const logState = ref(false)

    function setStoreUserInfo(UserInfo:IStoreUser) {
        storeUserInfo.value = UserInfo
        console.log(storeUserInfo);
        token.value = UserInfo.token
        logState.value = true
    }
     function logout() {
        storeUserInfo.value = null
        token.value = ''
        logState.value = false
    }

    return {
        storeUserInfo,
        setStoreUserInfo,
        logState,
        token,
        logout
    }
},
{persist:true}
)