import get from "lodash/get";

export default function ({ module, name, perPage, include, filters, value, text, variable, url }) {
  if (!url) {
    url = () => `/api/data/${module}`;
  }
  let calcFilters;
  let headers = {
    'Accept': 'application/json',
    'Content-Type': 'application/json',
  };
  if (filters instanceof Function) {
    calcFilters = filters;
  } else {
    calcFilters = () => filters;
  }
  const capName = name.charAt(0).toUpperCase() + name.slice(1);
  return {
    data() {
      return {
        [name]: {
          current_page: 1,
          data: [],
          from: 1,
          last_page: 0,
          per_page: perPage,
          to: 0,
          total: 0,
          include: "",
        }
      };
    },
    computed: {
      [`url${capName}`]: url,
      [`filters${capName}`]: calcFilters,
    },
    methods: {
      async [`fetch${capName}`](inParams = {}) {
        const urlBase = this[`url${capName}`];
        if (!urlBase) {
          return;
        }
        const params = JSON.parse(JSON.stringify(inParams));
        if (variable) {
          const currentValue = get(this, variable);
        }
        const urlParams = new URLSearchParams();
        // prepare parameters
        if (params) {
          Object.keys(params).forEach((key) => {
            if (params[key] instanceof Array) {
              params[key].forEach((paramValue) => {
                urlParams.append(key + "[]", paramValue);
              });
            } else if (params[key] !== undefined && params[key] !== null) {
              urlParams.append(key, params[key]);
            }
          });
        }
        // restore missing params
        if (params.include === undefined && include) {
          urlParams.append("include", include);
        }
        if (params.per_page === undefined) {
          urlParams.append("per_page", this[name].per_page ?? 10);
        }
        // store default params
        if (params.per_page) {
          this[name].per_page = params.per_page;
        }
        let data = await fetch(urlBase + "?" + urlParams.toString());
        data = await data.json();
        data.data.forEach((item) => {
          item.value = get(item, value);
          item.text = get(item, text);
        });
        Object.assign(this[name], data);
      },
      [`navigate${capName}`](page, params = {}) {
        const filter = [];
        const filtersC = this[`filters${capName}`];
        if (filtersC) {
          filtersC.forEach(({ where, params }) => {
            const paramsValues = JSON.stringify(params);
            filter.push(`${where}(${paramsValues.substring(1, paramsValues.length - 1)})`);
          });
        }
        return this[`fetch${capName}`]({ page: page, filter, ...params });
      },
      [`refresh${capName}`](page, params = {}) {
        return this[`navigate${capName}`](1);
      },
      async [`create${capName}`](data) {
        const urlBase = this[`url${capName}`];
        if (!urlBase) {
          return;
        }
        const response = await fetch(urlBase, {
          method: 'POST',
          body: JSON.stringify(data),
          headers: headers,
        });
        if (!(response.status >= 200 && response.status < 300)) {
          const json = await response.json();
          throw new Error(json.error || response.statusText);
        }
        let result = await response.json();
        return result;
      },
      async [`delete${capName}`](id) {
        const urlBase = this[`url${capName}`];
        if (!urlBase) {
          return;
        }
        const response = await fetch(`${urlBase}/${id}`, {
          method: 'DELETE',
          headers: headers,
        });
        if (!(response.status >= 200 && response.status < 300)) {
          const json = await response.json();
          throw new Error(json.error || response.statusText);
        }
        return true;
      },
    },
    created() {
      this[`navigate${capName}`](1, {
        include,
        per_page: perPage,
      });
    },
    watch: {
      [`url${capName}`]() {
        this[`navigate${capName}`](1, {});
      },
      [`filters${capName}`]() {
        this[`navigate${capName}`](1, {});
      }
    },
  };
}

