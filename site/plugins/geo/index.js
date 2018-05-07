panel.plugin("distantnative/field-geo", {
  use: {
    "$geo": {
      install(Vue) {
        Vue.prototype.$geo = {
          instances: 0,
          ready: false,
          load(store) {
            return new Promise((resolve, reject) => {
              this.instances++;

              if (this.instances > 1) {
                resolve();
                return;
              }

              window.loadedGoogleMaps = () => {
                this.ready = true;
                resolve();
              }

              let script = document.createElement('script');
              script.setAttribute('src',
              `https://maps.googleapis.com/maps/api/js?key=AIzaSyAe7qYXp9ZiU0WniGiNBBeYclAo4tc5yY0&libraries=places&language=${store.state.translation.id}&callback=loadedGoogleMaps`);
              document.body.appendChild(script);
            });
          },
          loaded() {
            return new Promise((resolve, reject) => {
              const interval = setInterval(() => {
                if (this.ready) {
                  clearInterval(interval);
                  resolve();
                }
              }, 10);
            });
          },
          create(element, options) {
            return new google.maps.Map(element, Object.assign(options, {
              center: new google.maps.LatLng(options.lat, options.lng),
              disableDefaultUI: true,
              gestureHandling: 'none',
            }));
          }
        };
      }
    }
  },
  fields: {
    geo: {
      props: {
        value: [Object, Array]
      },
      data() {
        return {
          state: this.toState(this.value),
          place: null,
          map: null,
          marker: null,
          search: null
        };
      },
      computed: {
        hasChanged() {
          return this.value.lat !== this.toValue.lat || this.value.lng !== this.toValue.lng;
        },
        defaultLocation() {
          return {
            lat:  52.5125702,
            lng:  13.389289299999973,
            zoom: 14
          }
        }
      },
      watch: {
        value() {
          this.toState(this.value);
        }
      },
      mounted() {
        this.load();
      },
      methods: {
        clearMarker() {
          if (this.marker) {
            this.marker.setMap(null);
          }
        },
        clearInput() {
          if (this.$refs.input) {
            this.$refs.input.value = "";
          }
        },
        load() {
          this.$geo.load(this.$store).then(() => {

            this.map = this.$geo.create(
              this.$refs.map,
              {
                ...this.defaultLocation,
                ...this.state
              }
            );

            this.search = new google.maps.places.Autocomplete(this.$refs.input);
            this.search.addListener("place_changed", () => {
              this.result(this.search.getPlace());
            });
          });
        },
        lookup(lat, lng) {
          this.$geo.loaded().then(() => {
            new google.maps.Geocoder().geocode({
              location: {
                lat: lat,
                lng: lng
              }
            }, (results, status) => {
              this.clearInput();

              if (results[0]) {
                this.place = results[0];
                this.setMarker(this.place.geometry.location);
              } else {
                this.place = null;
                this.clearMarker();
              }
            });
          });
        },
        onInput(value) {
          this.state = value;
          this.$emit("input", value);
        },
        reset() {
          this.place = null;
          this.clearMarker();
          this.clearInput();
          this.onInput({});
        },
        result(result, emit = true) {
          this.place = result;

          this.onInput({
            lat: this.place.geometry.location.lat(),
            lng: this.place.geometry.location.lng()
          });

          this.setMarker(this.place.geometry.location);
        },
        setMarker(location) {
          this.clearMarker();
          this.marker = new google.maps.Marker({
            position: location,
            map: this.map
          });
          this.map.panTo(location);
        },
        toState(value) {
          if (!value.lat || !value.lng) {
            this.place = null;
            this.clearMarker();
            this.clearInput();
            return {};
          }

          value.lat = parseFloat(value.lat);
          value.lng = parseFloat(value.lng);

          if (
            !this.place || (
              value.lat !== this.place.geometry.location.lat() ||
              value.lng !== this.place.geometry.location.lng()
            )
          ) {
            this.lookup(value.lat, value.lng);
          }

          return value;
        }
      },
      template: `
        <kirby-field :input="_uid" v-bind="$props" class="kirby-geo-field">
          <kirby-input theme="field">
            <input
              ref="input"
              :id="_uid"
              :placeholder="$t('plugin.geo.search.placeholder')"
              class="kirby-text-input"
              @keydown.enter.prevent
              type="text"
            />
            <div ref="map" class="kirby-geo-field-map"></div>
            <div v-if="place" class="kirby-geo-field-info">
              <strong>{{ place.name }}</strong> {{ place.formatted_address }}
              <kirby-button icon="cancel" @click.native="reset"></kirby-button>
            </div>
          </kirby-input>
        </kirby-field>
      `
    }
  },
  translations: {
    en: {
      "plugin.geo.search.placeholder": "Type to find place …"
    },
    de: {
      "plugin.geo.search.placeholder": "Tippe um zu suchen …"
    }
  }
});
