import store2 from 'store2';
import { getBase } from './index';

let name = getBase()
const store = store2.namespace(name);

const localData = {
    get(name, defaultValue = null) {
        const value = store.get(name)
        return value || defaultValue
    },

    set(name, value) {
        store.set(name, value)
    },

    remove(name) {
        store.remove(name)
    },

    clearAll() {
        store.clearAll()
    }
};

export default localData;