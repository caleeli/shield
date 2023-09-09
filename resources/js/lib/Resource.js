
export default class Resource {
    static index(array, url, params) {
        axios.get(url, {params: params}).then(response => {
            array.splice(0, array.length, ...response.data.data);
        });
        return array;
    }
}
