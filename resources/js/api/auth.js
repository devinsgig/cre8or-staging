export function login(user) {
    return window.axios.post('auth/login', user);
}

export function logout() {
    return window.axios.get('auth/logout');
}

export function signup(user) {
    return window.axios.post('auth/signup', user);
}
export function getSignupConfig(){
    return window.axios.get(`auth/signup/config`).then((response) => {
        return response.data.data;
    });
}