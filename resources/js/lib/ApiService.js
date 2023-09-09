const headers = {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
    'X-CSRF-TOKEN': document.head.querySelector('meta[name="csrf-token"]').content
};


class ApiService {

    baseUrl = '/api/data/';
    error = null;
    errorMessage = "";
    checkingSession = false;

    logError(message, error) {
        this.errorMessage = message;
        this.error = error;
        if (String(error.message) === '401') {
            this.checkSessionActive();
        }
        throw error;
    }

    completeUrl(url) {
        if (url.startsWith("/")) {
            return url;
        } else {
            return this.baseUrl + url;
        }
    }

    /**
     * Initiates an asynchronous fetch to the given URL and immediately returns
     * the provided defaultData. When the fetch operation completes, defaultData
     * will be populated with the data returned from the API.
     *
     * @param {string} url
     * @param {object} options
     * @param {array|object} defaultData
     * @returns {array|object}
     */
    lazyLoadData(url, options = {}, defaultData = [], callback = null) {
        const fullUrl = this.completeUrl(url);
        fetch(fullUrl, {
            headers,
            ...options,
        }).then((response) => {
            if (!response.ok) {
                throw new Error(response.status);
            }
            return response.json();
        }).then((data) => {
            if (defaultData instanceof Array) {
                if (callback) {
                    defaultData.push(...data.data.map(callback));
                } else {
                    defaultData.push(...data.data);
                }
            } else {
                if (callback) {
                    data = callback(data);
                }
                Object.assign(defaultData, data);
            }
        }).catch((error) => {
            this.logError("Error al cargar los datos del servidor", error);
        });

        return defaultData;
    }

    selectObject(url, options = {}, defaultData = {}, callback = null) {
        return this.lazyLoadData(url, options, defaultData, callback);
    }

    selectList(url, idField = "id", textField = "descripcion", params = null, defaultData = [{ value: null, text: "" }]) {
        const hasQuestion = url.indexOf("?") !== -1;
        let selectUrl = url + (hasQuestion ? "&" : "?") + "per_page=1000&fields=" + idField + "," + textField;
        // prepare extra query parameters
        if (params) {
            const urlParams = new URLSearchParams();
            Object.keys(params).forEach((key) => {
                if (params[key] instanceof Array) {
                    params[key].forEach((paramValue) => {
                        urlParams.append(key + "[]", paramValue);
                    });
                } else if (params[key] !== undefined && params[key] !== null) {
                    urlParams.append(key, params[key]);
                }
            });
            selectUrl += "&" + urlParams.toString();
        }
        return this.lazyLoadData(selectUrl, {}, defaultData, (item) => ({ value: item[idField], text: item[textField] }));
    }

    fetchDataFrom(model) {
        const baseUrl = model.substring(0, 1) === "/" ? "" : this.baseUrl;
        const table = {
            totalRows: 0,
            error: "",
            items: async function (ctx) {
                const currentPage = ctx.currentPage;
                const perPage = ctx.perPage;
                let filter = "";
                if (ctx.filter && typeof ctx.filter === "object") {
                    Object.keys(ctx.filter)
                        .filter((key) => ctx.filter[key])
                        .forEach((key) => {
                            // uppercase first
                            const where = "where" + key.charAt(0).toUpperCase() + key.slice(1);
                            filter += `&filter[]=${where}(${JSON.stringify(ctx.filter[key])})`;
                        });
                } else if (ctx.filter && typeof ctx.filter === "string") {
                    filter = `&filter[]=whereSearch(${JSON.stringify(ctx.filter)})`;
                }

                // Add any additional parameters to the URL as per your API requirements
                const queryMark = model.indexOf("?") === -1 ? "?" : "&";
                const url = `${baseUrl}${model}${queryMark}page=${currentPage}&per_page=${perPage}${filter}`;

                const response = await fetch(url, { headers });
                const data = await response.json();

                if (!response.ok) {
                    table.error = data.message;
                    return [];
                }

                // this.totalRows = data.totalRows; // Update this to match the property in your API response
                table.totalRows = data.total; // Update this to match the property in your API response

                // This assumes your API returns an array of items
                // Adjust as necessary to match your API response structure
                return data.data;
            },
        };
        return table;
    }

    selectTable(model) {
        return this.fetchDataFrom(model);
    }

    async post(url, data, options = {}) {
        const fullUrl = this.completeUrl(url);
        try {
            const response = await fetch(fullUrl, {
                method: "POST",
                headers,
                body: JSON.stringify(data),
                ...options,
            });
            if (!response.ok) {
                throw new Error(response.status);
            }
            return await response.json();
        } catch (error) {
            this.logError("Error al cargar los datos del servidor", error);
        }
    }

    async get(url, options = {}) {
        const fullUrl = this.completeUrl(url);
        try {
            const response = await fetch(fullUrl, {
                method: "GET",
                headers,
                ...options,
            });
            if (!response.ok) {
                throw new Error(response.status);
            }
            return await response.json();
        } catch (error) {
            this.logError("Error al cargar los datos del servidor", error);
        }
    }

    async delete(url, options = {}) {
        const fullUrl = this.completeUrl(url);
        try {
            const response = await fetch(fullUrl, {
                method: "DELETE",
                headers,
                ...options,
            });
            if (!response.ok) {
                throw new Error(response.status);
            }
            return await response.text();
        } catch (error) {
            this.logError("Error al cargar los datos del servidor", error);
        }
    }

    async put(url, data, options = {}) {
        const fullUrl = this.completeUrl(url);
        try {
            const response = await fetch(fullUrl, {
                method: "PUT",
                headers,
                body: JSON.stringify(data),
                ...options,
            });
            if (!response.ok) {
                throw new Error(response.status);
            }
            return await response.json();
        } catch (error) {
            this.logError("Error al cargar los datos del servidor", error);
        }
    }

    async checkSessionActive() {
        if (this.checkingSession) {
            return;
        }
        console.log('checking session');
        try {
            this.checkingSession = true;
            await this.get("check-session");
            this.checkingSession = false;
        } catch (error) {
            // redirect to login
            window.location.href = "/";
        }
    }
}

export default ApiService;
