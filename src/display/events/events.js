
// Three.js
import * as THREE from 'three';
import pako from 'pako';

// Physics Geometry Library
import PHYS from '../shapes/PHYS.js';
import HEP from './hep.js';

//function degrees_to_radians(deg) { return (deg * Math.PI) / 180.0 }
function radians_to_degrees(rad) { return (rad * 180.0) / Math.PI }

export default class Events {

    constructor() {
        // container for settings
        this.settings = {};

		this.text = false; // raw event as text
		this.evt = false;  // parsed event as js object

        // progress callbacks
        this.callbacks = {
            onStart: false,     // no parameters
            onProgress: false,  // ( "text", percentage 1..100 )
            onFinish: false     // no parameters
        };

        // primary container for event objects
        this.group = new THREE.Group();

		// hits placeholder
		this.hits = new THREE.Group();
		this.group.add( this.hits );

		// tracks placeholder
		this.tracks = new THREE.Group();
		this.group.add( this.tracks );

		this.materials = {};
		this.materials.lines = new THREE.LineBasicMaterial({ color: 0xffffff, opacity: 1, linewidth: 1,
																vertexColors: THREE.VertexColors });
		this.materials_extra = {};

		this.track_color_theme = 'blue';

		this.cuts = {
			tracks: {
				enabled: false,
				preserve: false,
				pt:  { min: false, max: false, cmin: false, cmax: false, mode: true },	// autodetect min/max; mode: inc/exc
				p:   { min: false, max: false, cmin: false, cmax: false, mode: true },	// autodetect min/max; mode: inc/exc
				eta: { min: false, max: false, cmin: false, cmax: false, mode: true },	// autodetect min, max; mode: include / exclude
				phi: { min: false, max: false, cmin: false, cmax: false, mode: true },	// radian, autodetect min, max; mode: include / exclude
				charge: 0 // [ 0: 'all', -1: 'neg', 1: 'pos' ]
			},
			hits: {
				enabled: false,
				preserve: false,
				e: { min: false, max: false, cmin: false, cmax: false, mode: true },	// energy
				eta: { min: false, max: false, cmin: false, cmax: false, mode: true },	// autodetect min/max; mode: include/exclude
				phi: { min: false, max: false, cmin: false, cmax: false, mode: true },	// autodetect min/max; mode: include/exclude
				r: { min: 0, max: false, cmin: false, cmax: false, mode: true },		// autodetect max; mode: include/exclude
				z: { min: false, max: false, cmin: false, cmax: false, mode: true }		// autodetect min/max; mode: include/exclude range
			}
		};

		this.scuts = {
			tracks: {},
			hits  : {}
		};

		// known hit types:
		this.hitTypes = {
			"HEX": {
				"dz": 5,
				"radius": 50,
				"rotation": 0,
				"color": 0xffffff
			},
			"3D": {
				"size": 3, 			// default size
				"color": 0xffffff	// default color
			},
			"PROJECTIVE": {
				"deta": 0.025, // default deta/dphi cone
				"dphi": 0.025,
				"scaleminmax": true,
				"color": 0xffffff
			},
			"STACKED-PROJECTIVE": {
				"deta": 0.025, // default deta/dphi cone
				"dphi": 0.025,
				"scaleminmax": true,
				"color": 0xffffff
			},
			"JET": {
				"rmin": 0,
				"rmax": 78,
				"emin": 0,
				"emax": 30,
				"color": 16777215,
				"transparent": 0.5
			},
			"EPD": {
				"zmin": 316.0,
				"zmax": 346.0,
				"dphi": 7.5,
				"dy":   2.7,
				"color": [ 10671210, 16777215 ],
				"transparent": 0.5,
				"scaleminmax": true
			},
			"BOX": {
				"dx": 5,
				"dy": 5,
				"dz": 5,
				"color": 0xffffff,
				"axis": "+z",
				"scaleminmax": true
			}
		};
	}

    set cbOnStart( val )    { this.callbacks.onStart = val;     }
    set cbOnProgress( val ) { this.callbacks.onProgress = val;  }
    set cbOnFinish( val )   { this.callbacks.onFinish = val;    }

    async load( url ) {
        if ( this.callbacks.onStart ) { this.callbacks.onStart(); }
        return new Promise((resolve, reject) => {
            this.text = false;
            let loader = new THREE.FileLoader();
			if ( url.endsWith('.json.gz') ) {
				loader.setResponseType('arraybuffer');
			}
            loader.crossOrigin = 'anonymous';
            loader.setPath( this.path );
            loader.load( url, ( text ) => {
                    if ( this.callbacks.onFinish ) { this.callbacks.onFinish(); }
                    // onload
					if ( url.endsWith('.json.gz') ) {
						this.text = pako.inflate( text, { to: 'string' } );
					} else if ( url.endsWith('.json') ) {
						this.text = text;
					} else {
						reject();
					}
                    return resolve();
            }, ( progress ) => {
                if ( progress.lengthComputable && this.callbacks.onProgress ) {
                    let percentage = Math.floor( progress.loaded / progress.total * 100 );
                    this.callbacks.onProgress( "loading file..", percentage );
                }
            },
            ( error ) => {
                // onerror
                if ( this.callbacks.onFinish ) { this.callbacks.onFinish(); }
                console.log( 'ERROR - url failed to load - ' + url );
                console.log( error );
                reject();
            });
        });
    }

	get_event() {
		return this.group;
	}

    clear_container( container ) {
        for ( var i = container.children.length - 1; i >= 0; i-- ) {
            container.remove( container.children[i] );
        }
    }

	parse( text = this.text ) {
		console.log('-- parsing event');
		this.evt = JSON.parse( text );
		console.log('-- processing track cuts');
		this.parse_tracks_cuts( this.evt );

		console.log('-- processing hits scuts');
		this.parse_hits_scuts( this.evt );
		console.log('-- processing hits cuts');
		this.parse_hits_cuts( this.evt );

		console.log('-- parsing hits');
		this.parse_hits( this.evt );
		console.log('-- parsing tracks');
		this.parse_tracks( this.evt );
		console.log('-- done parsing');
		return this.group;
	}

	parse_tracks_cuts( evt ) {
		let minp = false, minpt = false, maxp = false, maxpt = false,
			mineta = false, maxeta = false, minphi = false, maxphi = false,
			tp, tpt, teta, tphi, vec = new HEP.ThreeVector(0,0,0), trk;
		if ( evt.TRACKS ) {
			for ( let i in evt.TRACKS ) {
				for ( let j in evt.TRACKS[i]) {
					trk = evt.TRACKS[i][j];
					if ( trk.pxyz !== undefined ) {
						// track is helix-defined
						vec.set( trk.pxyz[0], trk.pxyz[1], trk.pxyz[2] );
						tp  = vec.mag();
						tpt = vec.perp();
						tphi = vec.phi();
						teta = vec.pseudoRapidity();
						if ( minp   === false ) { minp   = tp;   }
						if ( maxp   === false ) { maxp   = tp;   }
						if ( minpt  === false ) { minpt  = tpt;  }
						if ( maxpt  === false ) { maxpt  = tpt;  }
						if ( mineta === false ) { mineta = teta; }
						if ( maxeta === false ) { maxeta = teta; }
						if ( minphi === false ) { minphi = tphi; }
						if ( maxphi === false ) { maxphi = tphi; }
						if ( tp < minp ) { minp = tp; }
						else if ( tp > maxp ) { maxp = tp; }
						if ( tpt < minpt ) { minpt = tpt; }
						else if ( tpt > maxpt ) { maxpt = tpt; }
						if ( tphi < minphi ) { minphi = tphi; }
						else if ( tphi > maxphi ) { maxphi = tphi; }
						if ( teta < mineta ) { mineta = teta; }
						else if ( teta > maxeta ) { maxeta = teta; }
					} else {
						// not helix-defined, try scalar values if provided
						if ( trk.pt !== undefined ) {
							if ( minpt === false ) { minpt = trk.pt; }
							if ( maxpt === false ) { maxpt = trk.pt; }
							if ( trk.pt < minpt  ) { minpt = trk.pt; }
							else if ( trk.pt > maxpt ) { maxpt = trk.pt; }
						}
						if ( trk.p !== undefined ) { // phi
							if ( minphi === false ) { minphi = trk.p; }
							if ( maxphi === false ) { maxphi = trk.p; }
							if ( trk.p < minphi ) { minphi = trk.p; }
							else if ( trk.p > maxphi ) { maxphi = trk.p; }
						}
						if ( trk.e !== undefined ) { // eta
							if ( mineta === false ) { mineta = trk.e; }
							if ( maxeta === false ) { maxeta = trk.e; }
							if ( trk.e < mineta ) { mineta = trk.e; }
							else if ( trk.e > maxeta ) { maxeta = trk.e; }
						}
						// strawman solution: eta, phi via vector from [0,0,0] to pts[0]
						if ( !trk.e && !trk.p && trk.pts ) {
							vec.set( trk.pts[0][0], trk.pts[0][1], trk.pts[0][2] );
							tphi = vec.phi();
							teta = vec.pseudoRapidity();
							if ( mineta === false ) { mineta = teta; }
							if ( maxeta === false ) { maxeta = teta; }
							if ( minphi === false ) { minphi = tphi; }
							if ( maxphi === false ) { maxphi = tphi; }
							if ( tphi < minphi ) { minphi = tphi; }
							else if ( tphi > maxphi ) { maxphi = tphi; }
							if ( teta < mineta ) { mineta = teta; }
							else if ( teta > maxeta ) { maxeta = teta; }
						}
					}
				}
			}
		} // if ( evt.TRACKS )

		// done, fill in params
		if ( minp !== false && maxp !== false && minp <= maxp ) {
			this.cuts.tracks.p.min = minp;
			this.cuts.tracks.p.max = maxp;
			if ( !this.cuts.tracks.preserve ) {
				this.cuts.tracks.p.cmin = minp;
				this.cuts.tracks.p.cmax = maxp;
			}
		}
		if ( minpt !== false && maxpt !== false && minpt <= maxpt ) {
			this.cuts.tracks.pt.min = minpt;
			this.cuts.tracks.pt.max = maxpt;
			if ( !this.cuts.tracks.preserve ) {
				this.cuts.tracks.pt.cmin = minpt;
				this.cuts.tracks.pt.cmax = maxpt;
			}
		}
		if ( mineta !== false && maxeta !== false && mineta < maxeta ) {
			this.cuts.tracks.eta.min = mineta;
			this.cuts.tracks.eta.max = maxeta;
			if ( !this.cuts.tracks.preserve ) {
				this.cuts.tracks.eta.cmin = mineta;
				this.cuts.tracks.eta.cmax = maxeta;
			}
		}
		if ( minphi !== false && maxphi !== false && minphi < maxphi ) {
			this.cuts.tracks.phi.min = minphi;
			this.cuts.tracks.phi.max = maxphi;
			if ( !this.cuts.tracks.preserve ) {
				this.cuts.tracks.phi.cmin = minphi;
				this.cuts.tracks.phi.cmax = maxphi;
			}
		}
		this.cuts.tracks.enabled = true; // FIXME:
	}

	parse_hits_scuts( evt ) {
		if ( !evt.HITS ) { return; }
		for ( let i in evt.HITS ) {
			let mine = false, maxe = false,
				mineta = false, maxeta = false,
				minphi = false, maxphi = false,
				minz = false, maxz = false,
				minr = false, maxr = false,
				teta, tphi, tr, sume,
				vec = new HEP.ThreeVector(0,0,0), hit;
			this.scuts.hits[i] = JSON.parse( JSON.stringify( this.cuts.hits ) ); // default init
			for ( let j in evt.HITS[i]) {
				hit = evt.HITS[i][j];
				if ( hit.e !== undefined ) {
					if ( Array.isArray( hit.e ) ) {
						sume = hit.e.reduce((a, b) => a + b, 0);
						if ( mine === false ) { mine = sume; }
						if ( maxe === false ) { maxe = sume; }
						if ( sume < mine ) { mine = sume; }
						if ( sume > maxe ) { maxe = sume; }
					} else {
						hit.e = parseFloat( hit.e );
						if ( mine === false ) { mine = hit.e; }
						if ( maxe === false ) { maxe = hit.e; }
						if ( hit.e < mine ) { mine = hit.e; }
						if ( hit.e > maxe ) { maxe = hit.e; }
					}
				}
				if ( hit.eta !== undefined ) {
					hit.eta = parseFloat( hit.eta );
					if ( mineta === false ) { mineta = hit.eta; }
					if ( maxeta === false ) { maxeta = hit.eta; }
					if ( hit.eta < mineta ) { mineta = hit.eta; }
					if ( hit.eta > maxeta ) { maxeta = hit.eta; }
				}
				if ( hit.phi !== undefined ) {
					hit.phi = parseFloat( hit.phi );
					if ( minphi === false ) { minphi = hit.phi; }
					if ( maxphi === false ) { maxphi = hit.phi; }
					if ( hit.phi < minphi ) { minphi = hit.phi; }
					if ( hit.phi > maxphi ) { maxphi = hit.phi; }
				}
				if ( hit.x !== undefined && hit.y !== undefined && hit.z !== undefined ) {
					hit.x = parseFloat( hit.x );
					hit.y = parseFloat( hit.y );
					hit.z = parseFloat( hit.z );
					vec.set( hit.x, hit.y, hit.z );
					tr = vec.perp();
					tphi = vec.phi();
					teta = vec.pseudoRapidity();

					if ( mineta === false ) { mineta = teta; }
					if ( maxeta === false ) { maxeta = teta; }
					if ( teta < mineta ) { mineta = teta; }
					if ( teta > maxeta ) { maxeta = teta; }

					if ( minphi === false ) { minphi = tphi; }
					if ( maxphi === false ) { maxphi = tphi; }
					if ( tphi < minphi ) { minphi = tphi; }
					if ( tphi > maxphi ) { maxphi = tphi; }

					if ( minz === false ) { minz = hit.z; }
					if ( maxz === false ) { maxz = hit.z; }
					if ( hit.z < minz ) { minz = hit.z; }
					if ( hit.z > maxz ) { maxz = hit.z; }

					if ( minr === false ) { minr = tr; }
					if ( maxr === false ) { maxr = tr; }
					if ( tr < minr ) { minr = tr; }
					if ( tr > maxr ) { maxr = tr; }
				}
			}
			if ( mine !== false && maxe !== false && mine <= maxe ) {
				this.scuts.hits[i].e.min = mine;
				this.scuts.hits[i].e.max = maxe;
				if ( !this.scuts.hits[i].preserve ) {
					this.scuts.hits[i].e.cmin = mine;
					this.scuts.hits[i].e.cmax = maxe;
				}
			}
			if ( mineta !== false && maxeta !== false && mineta < maxeta ) {
				this.scuts.hits[i].eta.min = mineta;
				this.scuts.hits[i].eta.max = maxeta;
				if ( !this.scuts.hits[i].preserve ) {
					this.scuts.hits[i].eta.cmin = mineta;
					this.scuts.hits[i].eta.cmax = maxeta;
				}
			}
			if ( minphi !== false && maxphi !== false && minphi < maxphi ) {
				this.scuts.hits[i].phi.min = minphi;
				this.scuts.hits[i].phi.max = maxphi;
				if ( !this.scuts.hits[i].preserve ) {
					this.scuts.hits[i].phi.cmin = minphi;
					this.scuts.hits[i].phi.cmax = maxphi;
				}
			}
			if ( minr !== false && maxr !== false && minr < maxr ) {
				this.scuts.hits[i].r.min = minr;
				this.scuts.hits[i].r.max = maxr;
				if ( !this.scuts.hits[i].preserve ) {
					this.scuts.hits[i].r.cmin = minr;
					this.scuts.hits[i].r.cmax = maxr;
				}
			}
			if ( minz !== false && maxz !== false && minz < maxz ) {
				this.scuts.hits[i].z.min = minz;
				this.scuts.hits[i].z.max = maxz;
				if ( !this.scuts.hits[i].preserve ) {
					this.scuts.hits[i].z.cmin = minz;
					this.scuts.hits[i].z.cmax = maxz;
				}
			}
			this.scuts.hits[i].enabled = true;
		}
		this.scuts.selected_hits = '';
	}

	parse_hits_cuts( evt ) {
		let mine = false, maxe = false,
			mineta = false, maxeta = false,
			minphi = false, maxphi = false,
			minz = false, maxz = false,
			minr = false, maxr = false,
			teta, tphi, tr, sume,
			vec = new HEP.ThreeVector(0,0,0), hit;
		if ( evt.HITS ) {
			for ( let i in evt.HITS ) {
				console.log('processing cuts for hit type: ' + i );
				for ( let j in evt.HITS[i]) {
					hit = evt.HITS[i][j];
					if ( hit.e !== undefined ) {
						if ( Array.isArray( hit.e ) ) {
							sume = hit.e.reduce((a, b) => a + b, 0);
							if ( mine === false ) { mine = sume; }
							if ( maxe === false ) { maxe = sume; }
							if ( sume < mine ) { mine = sume; }
							if ( sume > maxe ) { maxe = sume; }
						} else {
							hit.e = parseFloat( hit.e );
							if ( mine === false ) { mine = hit.e; }
							if ( maxe === false ) { maxe = hit.e; }
							if ( hit.e < mine ) { mine = hit.e; }
							if ( hit.e > maxe ) { maxe = hit.e; }
						}
					}
					if ( hit.eta !== undefined ) {
						hit.eta = parseFloat( hit.eta );
						if ( mineta === false ) { mineta = hit.eta; }
						if ( maxeta === false ) { maxeta = hit.eta; }
						if ( hit.eta < mineta ) { mineta = hit.eta; }
						if ( hit.eta > maxeta ) { maxeta = hit.eta; }
					}
					if ( hit.phi !== undefined ) {
						hit.phi = parseFloat( hit.phi );
						if ( minphi === false ) { minphi = hit.phi; }
						if ( maxphi === false ) { maxphi = hit.phi; }
						if ( hit.phi < minphi ) { minphi = hit.phi; }
						if ( hit.phi > maxphi ) { maxphi = hit.phi; }
					}
					if ( hit.x !== undefined && hit.y !== undefined && hit.z !== undefined ) {
						hit.x = parseFloat( hit.x );
						hit.y = parseFloat( hit.y );
						hit.z = parseFloat( hit.z );
						vec.set( hit.x, hit.y, hit.z );
						tr = vec.perp();
						tphi = vec.phi();
						teta = vec.pseudoRapidity();

						if ( mineta === false ) { mineta = teta; }
						if ( maxeta === false ) { maxeta = teta; }
						if ( teta < mineta ) { mineta = teta; }
						if ( teta > maxeta ) { maxeta = teta; }

						if ( minphi === false ) { minphi = tphi; }
						if ( maxphi === false ) { maxphi = tphi; }
						if ( tphi < minphi ) { minphi = tphi; }
						if ( tphi > maxphi ) { maxphi = tphi; }

						if ( minz === false ) { minz = hit.z; }
						if ( maxz === false ) { maxz = hit.z; }
						if ( hit.z < minz ) { minz = hit.z; }
						if ( hit.z > maxz ) { maxz = hit.z; }

						if ( minr === false ) { minr = tr; }
						if ( maxr === false ) { maxr = tr; }
						if ( tr < minr ) { minr = tr; }
						if ( tr > maxr ) { maxr = tr; }
					}
				}
			}
		} // if ( evt.HITS )
		// done, fill in params
		if ( mine !== false && maxe !== false && mine <= maxe ) {
			this.cuts.hits.e.min = mine;
			this.cuts.hits.e.max = maxe;
			if ( !this.cuts.hits.preserve ) {
				this.cuts.hits.e.cmin = mine;
				this.cuts.hits.e.cmax = maxe;
			}
		}
		if ( mineta !== false && maxeta !== false && mineta < maxeta ) {
			this.cuts.hits.eta.min = mineta;
			this.cuts.hits.eta.max = maxeta;
			if ( !this.cuts.hits.preserve ) {
				this.cuts.hits.eta.cmin = mineta;
				this.cuts.hits.eta.cmax = maxeta;
			}
		}
		if ( minphi !== false && maxphi !== false && minphi < maxphi ) {
			this.cuts.hits.phi.min = minphi;
			this.cuts.hits.phi.max = maxphi;
			if ( !this.cuts.hits.preserve ) {
				this.cuts.hits.phi.cmin = minphi;
				this.cuts.hits.phi.cmax = maxphi;
			}
		}
		if ( minr !== false && maxr !== false && minr < maxr ) {
			this.cuts.hits.r.min = minr;
			this.cuts.hits.r.max = maxr;
			if ( !this.cuts.hits.preserve ) {
				this.cuts.hits.r.cmin = minr;
				this.cuts.hits.r.cmax = maxr;
			}
		}
		if ( minz !== false && maxz !== false && minz < maxz ) {
			this.cuts.hits.z.min = minz;
			this.cuts.hits.z.max = maxz;
			if ( !this.cuts.hits.preserve ) {
				this.cuts.hits.z.cmin = minz;
				this.cuts.hits.z.cmax = maxz;
			}
		}

		this.cuts.hits.enabled = true;
	}

	check_hit_cuts( hit, sub = '' ) {
		let vec = new HEP.ThreeVector(0,0,0), teta, tphi, sume;

		if ( hit.e !== undefined ) {
			if ( Array.isArray( hit.e ) ) {
				sume = hit.e.reduce((a, b) => a + b, 0);
				if ( sub ) {
					if ( sume < this.scuts.hits[sub].e.cmin ) { return false; }
					if ( sume > this.scuts.hits[sub].e.cmax ) { return false; }
				} else {
					if ( sume < this.cuts.hits.e.cmin ) { return false; }
					if ( sume > this.cuts.hits.e.cmax ) { return false; }
				}
			} else {
				if ( sub ) {
					if ( hit.e < this.scuts.hits[sub].e.cmin ) { return false; }
					if ( hit.e > this.scuts.hits[sub].e.cmax ) { return false; }
				} else {
					if ( hit.e < this.cuts.hits.e.cmin ) { return false; }
					if ( hit.e > this.cuts.hits.e.cmax ) { return false; }
				}
			}
		}
		if ( hit.eta !== undefined ) {
			if ( sub ) {
				if ( hit.eta < this.scuts.hits[sub].eta.cmin ) { return false; }
				if ( hit.eta > this.scuts.hits[sub].eta.cmax ) { return false; }

			} else {
				if ( hit.eta < this.cuts.hits.eta.cmin ) { return false; }
				if ( hit.eta > this.cuts.hits.eta.cmax ) { return false; }
			}
		} else if ( hit.x !== undefined && hit.y !== undefined && hit.z !== undefined ) {
			// convert x, y, z to eta
			vec.set( hit.x, hit.y, hit.z );
            teta = vec.pseudoRapidity();
			if ( sub ) {
				if ( teta < this.scuts.hits[sub].eta.cmin ) { return false; }
				if ( teta > this.scuts.hits[sub].eta.cmax ) { return false; }
			} else {
				if ( teta < this.cuts.hits.eta.cmin ) { return false; }
				if ( teta > this.cuts.hits.eta.cmax ) { return false; }
			}
		}
		if ( hit.phi !== undefined ) {
			if ( sub ) {
				if ( hit.phi < this.scuts.hits[sub].phi.cmin ) { return false; }
				if ( hit.phi > this.scuts.hits[sub].phi.cmax ) { return false; }
			} else {
				if ( hit.phi < this.cuts.hits.phi.cmin ) { return false; }
				if ( hit.phi > this.cuts.hits.phi.cmax ) { return false; }
			}
		} else if ( hit.x !== undefined && hit.y !== undefined && hit.z !== undefined ) {
			// convert x, y, z to phi
			vec.set( hit.x, hit.y, hit.z );
            tphi = vec.phi();
			if ( sub ) {
				if ( tphi < this.scuts.hits[sub].phi.cmin ) { return false; }
				if ( tphi > this.scuts.hits[sub].phi.cmax ) { return false; }
			} else {
				if ( tphi < this.cuts.hits.phi.cmin ) { return false; }
				if ( tphi > this.cuts.hits.phi.cmax ) { return false; }
			}
		}
		return true;
	}

	check_cuts( p, c ) { // p = HEP.ThreeVector, c: charge
		if ( this.cuts.tracks.p.min !== false &&
			( ( p.mag() < this.cuts.tracks.p.cmin ) || ( p.mag() > this.cuts.tracks.p.cmax ) ) &&
			this.cuts.tracks.p.mode === true
		) { return false; }
		if ( this.cuts.tracks.pt.min !== false &&
			( ( p.perp() < this.cuts.tracks.pt.cmin ) || ( p.perp() > this.cuts.tracks.pt.cmax ) ) &&
			this.cuts.tracks.pt.mode === true
		) {  return false; }
		if ( this.cuts.tracks.eta.min !== false &&
			( ( p.pseudoRapidity() < this.cuts.tracks.eta.cmin ) || ( p.pseudoRapidity() > this.cuts.tracks.eta.cmax ) ) &&
			this.cuts.tracks.eta.mode === true
		) {  return false; }
		if ( this.cuts.tracks.phi.min !== false &&
			( ( p.phi() < this.cuts.tracks.phi.cmin ) || ( p.phi() > this.cuts.tracks.phi.cmax ) ) &&
			this.cuts.tracks.phi.mode === true
		) { return false; }
		if ( this.cuts.tracks.charge !== 0 && c !== undefined && c !== false && c !== this.cuts.tracks.charge ) { return false; }
		return true;
	}

	check_cuts_plain( pt, eta, phi, c ) {
		if ( this.cuts.tracks.pt.min !== false && pt !== undefined && pt !== false &&
			( ( pt < this.cuts.tracks.pt.cmin ) || ( pt > this.cuts.tracks.pt.cmax ) ) &&
			this.cuts.tracks.pt.mode === true
		) { return false; }
		if ( this.cuts.tracks.eta.min !== false && eta !== undefined && eta !== false &&
			( ( eta < this.cuts.tracks.eta.cmin ) || ( eta > this.cuts.tracks.eta.cmax ) ) &&
			this.cuts.tracks.eta.mode === true
		) { return false; }
		if ( this.cuts.tracks.phi.min !== false && phi !== undefined && phi !== false &&
			( ( phi < this.cuts.tracks.phi.cmin ) || ( phi > this.cuts.tracks.phi.cmax ) ) &&
			this.cuts.tracks.phi.mode === true
		) { return false; }
		if ( this.cuts.tracks.charge !== 0 && c !== undefined && c !== false && c !== this.cuts.tracks.charge ) { return false; }
		return true;
	}

	parse_tracks( evt ) {
		//
		// *** track format ***
		// "TRACKS"/text => "TPC"/text => [ <track1>: { pts: [ [x1,y1,z1], ... [xn,yn,zn] ] }, <track2>, ... <trackN> ]
		//

		this.clear_container( this.tracks );
		if ( evt.TRACKS ) {
			let meta = ( evt.META && evt.META.TRACKS ) ? evt.META.TRACKS : false;

			for ( let i in evt.TRACKS ) {
				let geometry = new THREE.Geometry(),
					colors = [], vertices = [], trk, color, material = this.materials.lines;

				if ( meta && meta[i] ) {
					if ( meta[i].depth === false || ( meta[i].thickness && meta[i].thickness !== undefined ) ) {
						this.materials_extra[ i ] =
							new THREE.LineBasicMaterial({
								color: 0xffffff, opacity: 1,
								linewidth: ( ( meta[i].thickness || 1 ) | 0 ),
								vertexColors: THREE.VertexColors
							});
						material = this.materials_extra[ i ];
						if ( meta[i].depth === false ) {
							material.depthWrite = false;
							material.depthTest = false;
							material.renderOrder = 1;
						}
					}
				}

				for ( let j in evt.TRACKS[i]) {

					const track_vertices = [];
					trk = evt.TRACKS[i][j];

					if ( meta && meta[i] && meta[i].cuts ) {
						if ( trk.pt !== undefined && meta[i].cuts.pt !== undefined && meta[i].cuts.pt.min !== undefined && trk.pt < meta[i].cuts.pt.min ) {
							continue;
						} else if ( trk.pt !== undefined && meta[i].cuts.pt !== undefined && meta[i].cuts.pt.min !== undefined && trk.pt > meta[i].cuts.pt.max ) {
							continue;
						} else if ( trk.pxyz !== undefined && meta[i].cuts.pt !== undefined && meta[i].cuts.pt.min !== undefined &&
							Math.sqrt( Math.pow( trk.pxyz[0],2 ) + Math.pow( trk.pxyz[1], 2 ) ) < meta[i].cuts.pt.min ) {
								continue;
						} else if ( trk.pxyz !== undefined && meta[i].cuts.pt !== undefined && meta[i].cuts.pt.max !== undefined &&
							Math.sqrt( Math.pow( trk.pxyz[0],2 ) + Math.pow( trk.pxyz[1], 2 ) ) > meta[i].cuts.pt.max ) {
							continue;
						}
					}

					if ( trk.xyz !== undefined && trk.pxyz !== undefined && trk.l !== undefined && trk.q !== undefined ) {
						if ( this.cuts.tracks.enabled === true &&
								this.check_cuts( new HEP.ThreeVector( trk.pxyz[0], trk.pxyz[1], trk.pxyz[2] ),
									( trk.c || trk.q || 0 ) ) === false ) {
							continue;
						}

						// helix-based track
						let p, o, step, h, color, pos1, pos2;
						p = new HEP.ThreeVector( trk.pxyz[0] * 10, trk.pxyz[1] * 10, trk.pxyz[2] * 10 );
						o = new HEP.ThreeVector( trk.xyz[0] * 10, trk.xyz[1] * 10, trk.xyz[2] * 10 );
						step = ( trk.l * 10 ) / trk.nh;
						h = new HEP.PhysicalHelix( p, o, evt.EVENT.B, trk.q );
						color = ( ( meta && meta[i] && meta[i].color ) ? meta[i].color : this.get_track_color( trk ) );
						for ( let k = 0; k < trk.nh; k++ ) {
							pos1 = h.at( step * k );
							if ( meta && meta[i] && meta[i].r_min !== undefined && Math.sqrt( pos1.x() * pos1.x() + pos1.y() * pos1.y() ) < meta[i].r_min ) {
								continue; }
							pos2 = h.at( step * ( k + 1 ) );
							if ( meta && meta[i] && meta[i].r_max !== undefined && Math.sqrt( pos2.x() * pos2.x() + pos2.y() * pos2.y() ) > meta[i].r_max ) {
								continue; }
							vertices.push( new THREE.Vector3( pos1.x(), pos1.y(), pos1.z() ) );
							vertices.push( new THREE.Vector3( pos2.x(), pos2.y(), pos2.z() ) );
							track_vertices.push( new THREE.Vector3( pos1.x(), pos1.y(), pos1.z() ) );
							track_vertices.push( new THREE.Vector3( pos2.x(), pos2.y(), pos2.z() ) );
							colors.push( new THREE.Color( color ) );
							colors.push( new THREE.Color( color ) );
						}

						if ( trk.pts ) {
							// treat points as hits on helix
							let hitGroup = new THREE.Group();
							this.hits.add( hitGroup );
							this.create_hits_3d_array( i, hitGroup, trk.pts, { size: 5, color: 0xffffff } );
						}

					} else if ( trk.pts ) {

						// point-based track
						if ( this.cuts.tracks.enabled === true &&
							this.check_cuts_plain( trk.pt, trk.e, trk.p, ( trk.c || trk.q || 0 ) ) === false ) {
							continue;
						}
						color = ( ( meta && meta[i] && meta[i].color ) ? meta[i].color : this.get_track_color( trk ) );
						for (let k = 0, len = trk['pts'].length - 1; k < len; k++) {
							vertices.push( new THREE.Vector3( trk.pts[k][0] * 10, trk.pts[k][1] * 10, trk.pts[k][2] * 10 ) );
							vertices.push( new THREE.Vector3( trk.pts[k+1][0] * 10, trk.pts[k+1][1] * 10, trk.pts[k+1][2] * 10 ) );
							colors.push( new THREE.Color( color ) );
							colors.push( new THREE.Color( color ) );

						}
						// treat points as hits on helix
						let hitGroup = new THREE.Group();
						this.hits.add( hitGroup );
						this.create_hits_3d_array( i, hitGroup, trk.pts, { size: 5, color: 0xffffff } );
					}

					if ( meta && meta[i] && meta[i].width ) {
						const curve_path = new THREE.CurvePath();
						for ( let tg = 0; tg < (track_vertices.length - 1); tg += 2 ) {
							const mpath = new THREE.LineCurve3(
								track_vertices[tg],
								track_vertices[tg+1]
							);
							curve_path.add( mpath );
						}
						// TubeGeometry( path : Curve, tubularSegments : Integer, radius : Float, radialSegments : Integer, closed : Boolean )
						const tgeo = new THREE.TubeGeometry( curve_path, ( vertices.length - 1 ), ( trk.width || meta[i].width || 1 ), ( meta[i].segments || 3 ), false );
						const color = ( meta[i].color || trk.trk_color || 0xffffff );
						const mmaterial = new THREE.MeshBasicMaterial({ color });
						const mmesh = new THREE.Mesh( tgeo, mmaterial );
						this.tracks.add( mmesh );
					}
				}

				if ( !meta || !meta[i] || !meta[i].width ) {
					geometry.vertices.push( ...vertices );
					geometry.colors.push( ...colors );
					this.tracks.add( new THREE.LineSegments( geometry, material ) );
				}

			}
		}
	}

	parse_hits( evt ) {

		this.clear_container( this.hits );
		if ( !evt.META || !evt.META.HITS || !evt.HITS ) {
			// console.log('INFO - no hits in the event');
			return;
		}
		let meta = evt.META.HITS, ehits = evt.HITS;
		for ( let i in ehits ) {
			console.log('processing hits of type ' + i );
			if ( !meta[i] ) {
				console.log('HIT group "' + i + '" has no meta descriptor, skipping');
				continue;
			}
			switch( meta[i].type ) {
				case '3D':
					this.parse_hits_3d( i, ehits[i], meta[i].options, meta[i] );
					break;
				case 'PROJECTIVE':
					this.parse_hits_projective( i, ehits[i], meta[i].options, meta[i] );
					break;
				case 'STACKED-PROJECTIVE':
					this.parse_hits_stacked_projective( i, ehits[i], meta[i].options, meta[i] );
					break;
				case 'HEX':
					this.parse_hits_hex( i, ehits[i], meta[i].options, meta[i] );
					break;
				case 'BOX':
					this.parse_hits_box( i, ehits[i], meta[i].options, meta[i] );
					break;
				case 'JET':
					this.parse_hits_jet( i, ehits[i], meta[i].options, meta[i] );
					break;
				case 'EPD':
					this.parse_hits_epd( i, ehits[i], meta[i].options, meta[i] );
					break;
				default:
					break;
			}
		}
	}

	parse_hits_3d( id, hits, options = this.hitTypes['3D'], meta = false ) {
		let hitGroup = new THREE.Group();
		this.hits.add( hitGroup );
		if ( Array.isArray( hits ) ) {
			this.create_hits_3d_array( id, hitGroup, hits, options, meta );
		} else {
			for( let i in hits ) {
				let hitSubGroup = new THREE.Group();
				hitGroup.add( hitSubGroup );
				this.create_hits_3d_array( id, hitSubGroup, hits[i], options, meta );
			}
		}
	}

	create_hits_3d_array( id, three_node, hits, options, meta = false ) {
		let combined_geometry = new THREE.Geometry(), h, geo;
		for( let i = 0, ilen = hits.length; i < ilen; i++ ) {
			h = hits[i];
      if ( !this.check_hit_cuts( h, id ) ) { continue; }
      if ( meta && meta.cuts && meta.cuts.e && meta.cuts.e.min && h.e < meta.cuts.e.min ) {
        continue;
      }
      if ( meta && meta.cuts && meta.cuts.e && meta.cuts.e.max && h.e > meta.cuts.e.max ) {
        continue;
      }
			geo = new PHYS.Geo.box({ dx: options.size, dy: options.size, dz: options.size });
			geo.applyMatrix( new THREE.Matrix4().makeTranslation( ( h.x === undefined ? h[0] : h.x ) * 10, (h.y === undefined ? h[1] : h.y ) * 10, ( h.z === undefined ? h[2] : h.z ) * 10 ) );
			combined_geometry.merge( geo );
		}
    three_node.add( new THREE.Mesh( combined_geometry,
      new THREE.MeshBasicMaterial({ color: options.color, transparent: options.transparent === undefined ? false : true,
        opacity: options.transparent === undefined ? 1 : options.transparent, side: THREE.DoubleSide }) ) );
	}

	parse_hits_projective( id, hits, options = this.hitTypes.PROJECTIVE, meta = false ) {
		let hitGroup = new THREE.Group();
		this.hits.add( hitGroup );
		if ( Array.isArray( hits ) ) {
			this.create_hits_projective_array( id, hitGroup, hits, options, meta );
		} else {
			for( let i in hits ) {
				let hitSubGroup = new THREE.Group();
				hitGroup.add( hitSubGroup );
				this.create_hits_projective_array( id, hitSubGroup, hits[i], options, meta );
			}
		}
	}

	parse_hits_stacked_projective( id, hits, options = this.hitTypes.PROJECTIVE, meta = false ) {
		let hitGroup = new THREE.Group();
		this.hits.add( hitGroup );
		if ( Array.isArray( hits ) ) {
			this.create_hits_stacked_projective_array( id, hitGroup, hits, options, meta );
		} else {
			for( let i in hits ) {
				let hitSubGroup = new THREE.Group();
				hitGroup.add( hitSubGroup );
				this.create_hits_stacked_projective_array( id, hitSubGroup, hits[i], options, meta );
			}
		}
	}

	calculate_hits_array_min_max( hits ) {
		let h, min = hits[0].e, max = hits[0].e, dminmax = 0.0001, sume;
		if ( Array.isArray(hits[0].e) ) {
			min = hits[0].e.reduce((a, b) => a + b, 0);
			max = hits[0].e.reduce((a, b) => a + b, 0);
		}
		for ( let i = 1, ilen = hits.length; i < ilen; i++ ) {
			h = hits[i];
			if ( Array.isArray( h.e ) ) {
				sume = h.e.reduce((a, b) => a + b, 0);
				if ( sume < min ) { min = sume; }
				else if ( sume > max ) { max = sume; }
			} else {
				if ( h.e < min ) { min = h.e; }
				else if ( h.e > max ) { max = h.e; }
			}
		}
		dminmax = Math.abs( max - min );
		return { min, max, dminmax };
	}

	create_hits_projective_array( id, hitSubGroup, hits, options, meta = false ) {
		if ( options.rmin !== undefined && options.rmax !== undefined ) {
			return this.create_hits_projective_r_array( id, hitSubGroup, hits, options, meta );
		} else if ( options.zmin !== undefined && options.zmax !== undefined ) {
			return this.create_hits_projective_z_array( id, hitSubGroup, hits, options, meta );
		}
		console.log('ERROR - unknown projective hit?');
	}

	create_hits_stacked_projective_array( id, hitSubGroup, hits, options, meta = false ) {
		if ( options.rmin !== undefined && options.rmax !== undefined ) {
			return this.create_hits_stacked_projective_r_array( id, hitSubGroup, hits, options, meta );
		}
		console.log('ERROR - unknown projective hit?');
	}


	create_hits_projective_r_array( id, three_node, hits, options, meta = false ) {

		let minmax = false;
		if ( options && options.scaleminmax ) {
			minmax = this.calculate_hits_array_min_max( hits );
		}
		if ( options && options.scaleminmax && options.scaleminmax.min !== undefined ) {
			minmax.min = options.scaleminmax.min;
		}
		if ( options && options.scaleminmax && options.scaleminmax.max !== undefined ) {
			minmax.max = options.scaleminmax.max;
		}
		if ( options && options.scaleminmax && options.scaleminmax.min !== undefined || options.scaleminmax.max !== undefined ) {
			minmax.dminmax = minmax.max - minmax.min;
		}

		let combined_geometry = new THREE.Geometry(), h, geo, rmax, ratio;

		for ( let i = 0, ilen = hits.length; i < ilen; i++ ) {
			h = hits[i];

			if ( !this.check_hit_cuts( h, id ) ) { continue; }

			if ( meta && meta.cuts && meta.cuts.e && meta.cuts.e.min && h.e < meta.cuts.e.min ) {
				continue;
			}
			if ( meta && meta.cuts && meta.cuts.e && meta.cuts.e.max && h.e > meta.cuts.e.max ) {
				continue;
			}

			rmax = options.rmax;
			if ( minmax !== false ) {
				if ( minmax.dminmax == 0 ) {
					ratio = 1.0;
				} else {
					ratio = ( h.e - minmax.min ) / ( minmax.dminmax || 1.0 );
					if ( ratio > 1.0 ) { ratio = 1.0; }
				}
				rmax = ( options.rmin + ( options.rmax - options.rmin ) * ratio );
			}

			geo = new PHYS.Geo.trd3({ eta: parseFloat(h.eta), phi: parseFloat(h.phi),
				deta: options.deta, dphi: options.dphi,
				rmin: options.rmin * 10., rmax: rmax * 10. });

			// face colors: array or single color
			let color = this.hitColorFunc( options.color, ratio );
			for ( let fc = 0; fc < geo.faces.length; fc++ ) {
				let face  = geo.faces[ fc ];
				face.color.setRGB( color.r, color.g, color.b );
			}

			combined_geometry.merge( geo );
		}

		three_node.add( new THREE.Mesh( combined_geometry,
			new THREE.MeshBasicMaterial({ color: 0xffffff, transparent: options.transparent === undefined ? false : true,
				opacity: options.transparent === undefined ? 1 : options.transparent, side: THREE.DoubleSide, vertexColors: THREE.FaceColors }) ) );

	}

	create_hits_stacked_projective_r_array( id, three_node, hits, options, meta = false ) {
		// e = [ 1, 2, 3 ] => fix min-max procedure or implement a new one

		let minmax = false, drminrmax = 1.;
		if ( options && options.scaleminmax ) {
			minmax = this.calculate_hits_array_min_max( hits );
		}
		if ( options && options.scaleminmax && options.scaleminmax.min !== undefined ) {
			minmax.min = options.scaleminmax.min;
		}
		if ( options && options.scaleminmax && options.scaleminmax.max !== undefined ) {
			minmax.max = options.scaleminmax.max;
		}
		if ( options && options.scaleminmax && options.scaleminmax.min !== undefined || options.scaleminmax.max !== undefined ) {
			minmax.dminmax = minmax.max - minmax.min;
		}

		let combined_geometry = new THREE.Geometry(), h, geo, rmax, ratio, sume;

		for ( let i = 0, ilen = hits.length; i < ilen; i++ ) {
			h = hits[i];
			if ( Array.isArray( h.e ) ) {
				sume = h.e.reduce((a, b) => a + b, 0);
				if ( sume <= 0 ) { continue; }
				if ( !this.check_hit_cuts( h, id ) ) {
					continue;
				}
				if ( meta && meta.cuts && meta.cuts.e && meta.cuts.e.min && sume < meta.cuts.e.min ) {
					continue;
				}
				if ( meta && meta.cuts && meta.cuts.e && meta.cuts.e.max && sume > meta.cuts.e.max ) {
					continue;
				}
			} else {
				continue; // NOT A stacked projective hit
			}

			// FULL RMIN/RMAX, NOT MULTIPLIED BY 10 (cm->mm)
			rmax = options.rmax;
			if ( minmax !== false ) {
				if ( minmax.dminmax == 0 ) {
					ratio = 1.0;
				} else {
					ratio = ( sume - minmax.min ) / minmax.dminmax;
					if ( ratio > 1.0 ) { ratio = 1.0; }
				}
				rmax = ( options.rmin + ( options.rmax - options.rmin ) * ratio );
				drminrmax = rmax - options.rmin;
			}

			let rstart = options.rmin, rend = 0, seratio;
			for( let se = 0, selen = h.e.length; se < selen; se++ ) {

				seratio = h.e[se] / sume;
				rend = rstart + drminrmax * seratio;

				geo = new PHYS.Geo.trd3({ eta: parseFloat(h.eta), phi: parseFloat(h.phi),
					deta: options.deta, dphi: options.dphi,
					rmin: rstart * 10., rmax: rend * 10. });

				// face colors: array or single color
				let color = this.hitColorFunc( options.color[se], ratio );
				for ( let fc = 0; fc < geo.faces.length; fc++ ) {
					let face  = geo.faces[ fc ];
					face.color.setRGB( color.r, color.g, color.b );
				}
				combined_geometry.merge( geo );
				rstart += drminrmax * seratio;
			}

		}

		three_node.add( new THREE.Mesh( combined_geometry,
			new THREE.MeshBasicMaterial({ color: 0xffffff, transparent: options.transparent === undefined ? false : true,
				opacity: options.transparent === undefined ? 1 : options.transparent, side: THREE.DoubleSide, vertexColors: THREE.FaceColors }) ) );

	}

	create_hits_projective_z_array( id, three_node, hits, options, meta = false ) {
		if ( options.scaleminmax === true ) {
			this.calculate_hits_array_min_max( hits );
		}
		let combined_geometry = new THREE.Geometry(), h, geo;
		for ( let i = 0, ilen = hits.length; i < ilen; i++ ) {
			h = hits[i];
			if ( !this.check_hit_cuts( h, id ) ) { continue; }
			if ( meta && meta.cuts && meta.cuts.e && meta.cuts.e.min && h.e < meta.cuts.e.min ) {
				continue;
			}
			if ( meta && meta.cuts && meta.cuts.e && meta.cuts.e.max && h.e > meta.cuts.e.max ) {
				continue;
			}

			// FIXME: shape needed
			console.error('ERROR: unimplemented shape for projective z hits');

			combined_geometry.merge( geo );
		}
		three_node.add( new THREE.Mesh( combined_geometry,
			new THREE.MeshBasicMaterial({ color: options.color, transparent: options.transparent === undefined ? false : true,
				opacity: options.transparent === undefined ? 1 : options.transparent, side: THREE.DoubleSide, }) ) );
	}

	parse_hits_hex( id, hits, options = this.hitTypes['HEX'], meta = false ) {
		let hitGroup = new THREE.Group();
		this.hits.add( hitGroup );
		if ( Array.isArray( hits ) ) {
			this.create_hits_hex_array( id, hitGroup, hits, options, meta );
		} else {
			for( let i in hits ) {
				let hitSubGroup = new THREE.Group();
				hitGroup.add( hitSubGroup );
				this.create_hits_hex_array( id, hitSubGroup, hits[i], options, meta );
			}
		}
	}

	parse_hits_box( id, hits, options = this.hitTypes['BOX'], meta = false ) {
		let hitGroup = new THREE.Group();
		this.hits.add( hitGroup );
		if ( Array.isArray( hits ) ) {
			this.create_hits_box_array( id, hitGroup, hits, options, meta );
		} else {
			for( let i in hits ) {
				let hitSubGroup = new THREE.Group();
				hitGroup.add( hitSubGroup );
				this.create_hits_box_array( id, hitSubGroup, hits[i], options, meta );
			}
		}
	}

	parse_hits_jet( id, hits, options = this.hitTypes['JET'], meta = false ) {
		let hitGroup = new THREE.Group();
		this.hits.add( hitGroup );
		if ( Array.isArray( hits ) ) {
			this.create_hits_jet_array( id, hitGroup, hits, options, meta );
		} else {
			for( let i in hits ) {
				let hitSubGroup = new THREE.Group();
				hitGroup.add( hitSubGroup );
				this.create_hits_jet_array( id, hitSubGroup, hits[i], options, meta );
			}
		}
	}

	parse_hits_epd( id, hits, options = this.hitTypes['EPD'], meta = false ) {
		let hitGroup = new THREE.Group();
		this.hits.add( hitGroup );
		if ( Array.isArray( hits ) ) {
			this.create_hits_epd_array( id, hitGroup, hits, options, meta );
		} else {
			for( let i in hits ) {
				let hitSubGroup = new THREE.Group();
				hitGroup.add( hitSubGroup );
				this.create_hits_epd_array( id, hitSubGroup, hits[i], options, meta );
			}
		}
	}

	// hit color processing
	hitColorFunc( colors, ratio ) {
		if ( Array.isArray( colors ) && colors.length >= 2 ) {
			let d = 1.0 / ( colors.length - 1 ),
				ind = Math.floor( ratio / d ),
				dist = ( ( ratio - ind * d ) / d ),
				color = ( new THREE.Color( colors[ ind ] ) ).lerp( new THREE.Color( colors[ ind + 1 ] ), dist );
			return color;
		} else if ( Array.isArray( colors ) && colors.length === 1 ) {
			return new THREE.Color( colors[0] );
		}
		return new THREE.Color( colors );
	}

	create_hits_hex_array( id, three_node, hits, options, meta = false ) {
		let combined_geometry = new THREE.Geometry(),
			h, geo, ratio, rdz = options.minmaxdz || options.dz;

		let minmax = false;
		if ( options && options.scaleminmax ) {
			minmax = this.calculate_hits_array_min_max( hits );
		}
		if ( options && options.scaleminmax && options.scaleminmax.min !== undefined ) {
			minmax.min = options.scaleminmax.min;
		}
		if ( options && options.scaleminmax && options.scaleminmax.max !== undefined ) {
			minmax.max = options.scaleminmax.max;
		}
		if ( options && options.scaleminmax && ( options.scaleminmax.min !== undefined || options.scaleminmax.max !== undefined ) ) {
			minmax.dminmax = minmax.max - minmax.min;
		}

		for( let i = 0, ilen = hits.length; i < ilen; i++ ) {
			h = hits[i];
			if ( !this.check_hit_cuts( h, id ) ) { continue; }
			if ( meta.cuts && meta.cuts.e && meta.cuts.e.min && h.e < meta.cuts.e.min ) { continue; }
			if ( meta.cuts && meta.cuts.e && meta.cuts.e.max && h.e > meta.cuts.e.max ) { continue; }

			if ( minmax !== false ) {
				ratio = ( h.e - minmax.min ) / minmax.dminmax;
				if ( ratio > 1.0 ) { ratio = 1.0; } else if ( ratio < 0 ) { ratio = 0.0000001; }
				if ( options.scalecut && ratio <= options.scalecut ) { continue; }
			} else {
				ratio = 1.0;
			}

			geo = new PHYS.Geo.cyl({ rmax: options.radius * 10., dz: ( rdz * ratio * 10. ), twist: false, numSegs: 6 });
			if ( options.rotation ) {
				geo.applyMatrix( new THREE.Matrix4().makeRotationZ( options.rotation ) );
			}
			geo.applyMatrix( new THREE.Matrix4().makeTranslation(
				h.x * 10,
				h.y * 10,
				h.z * 10 + ( h.z > 0 ? ( rdz * ratio * 10. ) : ( -rdz * ratio * 10. ) )
			) );

			// face colors: array or single color
			let color = this.hitColorFunc( options.color, ratio );
			for ( let fc = 0; fc < geo.faces.length; fc++ ) {
				let face  = geo.faces[ fc ];
				face.color.setRGB( color.r, color.g, color.b );
			}

			combined_geometry.merge( geo );
		}

		three_node.add(
			new THREE.Mesh( combined_geometry,
				new THREE.MeshBasicMaterial({ color:  0xffffff, transparent: options.transparent ? true : false,
					opacity: options.transparent || 1, side: THREE.BackSide, vertexColors: THREE.FaceColors })
			)
		);
		three_node.add(
			new THREE.Mesh( combined_geometry,
				new THREE.MeshBasicMaterial({ color:  0xffffff, transparent: options.transparent ? true : false,
					opacity: options.transparent || 1, side: THREE.FrontSide, vertexColors: THREE.FaceColors })
			)
		);

	}

	create_hits_box_array( id, three_node, hits, options, meta = false ) {

		let combined_geometry = new THREE.Geometry(),
			h, geo, ratio;

		let minmax = false;
		if ( options && options.scaleminmax ) {
			minmax = this.calculate_hits_array_min_max( hits );
		}
		if ( options && options.scaleminmax && options.scaleminmax.min !== undefined ) {
			minmax.min = options.scaleminmax.min;
		}
		if ( options && options.scaleminmax && options.scaleminmax.max !== undefined ) {
			minmax.max = options.scaleminmax.max;
		}
		if ( options && options.scaleminmax && ( options.scaleminmax.min !== undefined || options.scaleminmax.max !== undefined ) ) {
			minmax.dminmax = minmax.max - minmax.min;
		}

		for( let i = 0, ilen = hits.length; i < ilen; i++ ) {
			h = hits[i];
			if ( !this.check_hit_cuts( h, id ) ) { continue; }
			if ( meta.cuts && meta.cuts.e && meta.cuts.e.min && h.e < meta.cuts.e.min ) { continue; }
			if ( meta.cuts && meta.cuts.e && meta.cuts.e.max && h.e > meta.cuts.e.max ) { continue; }
			let d = { x: options.dx, y: options.dy, z: options.dz };
			if ( minmax !== false ) {
				ratio = ( h.e - minmax.min ) / minmax.dminmax;
				if ( ratio > 1.0 ) { ratio = 1.0; } else if ( ratio < 0 ) { ratio = 0.0000001; }
				if ( options.scalecut && ratio <= options.scalecut ) { continue; }
				d[options.axis] *= ratio;
			} else {
				ratio = 1.0;
			}
			geo = new PHYS.Geo.box({ dx: d.x, dy: d.y, dz: d.z });
			geo.applyMatrix( new THREE.Matrix4().makeTranslation( h.x * 10, h.y * 10, h.z * 10 ) );

			// face colors: array or single color
			let color = this.hitColorFunc( options.color, ratio );
			for ( let fc = 0; fc < geo.faces.length; fc++ ) {
				let face  = geo.faces[ fc ];
				face.color.setRGB( color.r, color.g, color.b );
			}

			combined_geometry.merge( geo );
		}

		three_node.add(
			new THREE.Mesh( combined_geometry,
				new THREE.MeshBasicMaterial({ color:  0xffffff, transparent: options.transparent ? true : false,
					opacity: options.transparent || 1, side: THREE.BackSide, vertexColors: THREE.FaceColors })
			)
		);
		three_node.add(
			new THREE.Mesh( combined_geometry,
				new THREE.MeshBasicMaterial({ color:  0xffffff, transparent: options.transparent ? true : false,
					opacity: options.transparent || 1, side: THREE.FrontSide, vertexColors: THREE.FaceColors })
			)
		);
	}

	create_hits_epd_array( id, three_node, hits, options, meta = false ) {

		let combined_geometry = new THREE.Geometry(),
			h, geo, ratio;

		let minmax = false;
		if ( options && options.scaleminmax ) {
			minmax = this.calculate_hits_array_min_max( hits );
		}
		if ( options && options.scaleminmax && options.scaleminmax.min !== undefined ) {
			minmax.min = options.scaleminmax.min;
		}
		if ( options && options.scaleminmax && options.scaleminmax.max !== undefined ) {
			minmax.max = options.scaleminmax.max;
		}
		if ( options && options.scaleminmax && options.scaleminmax.min !== undefined || options.scaleminmax.max !== undefined ) {
			minmax.dminmax = minmax.max - minmax.min;
		}

		let dphi = options.dphi,
			zmin = options.zmin,
			zmax = options.zmax,
			dz = 1.;

		for( let i = 0, ilen = hits.length; i < ilen; i++ ) {
			h = hits[i];
			if ( !this.check_hit_cuts( h, id ) ) { continue; }
			if ( meta.cuts && meta.cuts.e && meta.cuts.e.min && h.e < meta.cuts.e.min ) { continue; }
			if ( meta.cuts && meta.cuts.e && meta.cuts.e.max && h.e > meta.cuts.e.max ) { continue; }
			if ( minmax !== false ) {
				ratio = ( h.e - minmax.min ) / minmax.dminmax;
				if ( ratio > 1.0 ) { ratio = 1.0; } else if ( ratio < 0 ) { ratio = 0.0000001; }
				if ( options.scalecut && ratio <= options.scalecut ) { continue; }
			} else {
				ratio = 1.0;
			}

			let theta = 2.0 * Math.atan( Math.exp(-h.eta) );

			if ( Math.abs(theta) > 0.306 ) {
				console.log( 'THETA out of bounds (>0.306): ' + theta );
			}

			let dr = Math.abs( zmin * 10. * Math.tan( theta ) ),
				rmin1 = (dr - options.dy * 10.),
				rmax1 = (dr + options.dy * 10.),
				rmin2 = rmin1,
				rmax2 = rmax1;

			dz = ratio < 1. ? ( ( zmax - zmin ) * ratio ) : 1.; // autoscale
			dz *= 10.;

			let phi1 = ( radians_to_degrees(h.phi) - dphi ),
					phi2 = ( radians_to_degrees(h.phi) + dphi );

			geo = new PHYS.Geo.cons({ dz, rmin1, rmax1, rmin2, rmax2, phi1, phi2, twist: false, numSegs: 8 });
			if ( h.eta > 0 ) {
				geo.applyMatrix( new THREE.Matrix4().makeTranslation( 0, 0, zmin * 10. + dz ) );
			} else {
				geo.applyMatrix( new THREE.Matrix4().makeTranslation( 0, 0, -zmin * 10. - dz ) );
			}

			// face colors: array or single color
			let color = this.hitColorFunc( options.color, ratio );
			for ( let fc = 0; fc < geo.faces.length; fc++ ) {
				let face  = geo.faces[ fc ];
				face.color.setRGB( color.r, color.g, color.b );
			}

			combined_geometry.merge( geo );
		}

		three_node.add(
			new THREE.Mesh( combined_geometry,
				new THREE.MeshBasicMaterial({ color:  0xffffff, transparent: options.transparent ? true : false,
					opacity: options.transparent || 1, side: THREE.BackSide, vertexColors: THREE.FaceColors })
			)
		);
		three_node.add(
			new THREE.Mesh( combined_geometry,
				new THREE.MeshBasicMaterial({ color:  0xffffff, transparent: options.transparent ? true : false,
					opacity: options.transparent || 1, side: THREE.FrontSide, vertexColors: THREE.FaceColors })
			)
		);
	}


	create_hits_jet_array( id, three_node, hits, options, meta = false ) {

		let combined_geometry = new THREE.Geometry(),
			h = false, geo, ratio;

		if ( h && meta && options && options.escaleminmax ) {
			// TODO
		}

		for( let i = 0, ilen = hits.length; i < ilen; i++ ) {
			h = hits[i];

			let l = meta.options.rmin + ( meta.options.rmax - meta.options.rmin ) 
						* ( ( h.e - meta.options.emin ) / ( meta.options.emax - meta.options.emin ) ) / 2.,
				rmax = 2. * l * Math.tan( h.R / 2. );

			geo = new PHYS.Geo.cone({ dz: l * 10., rmin1: 0, rmax1: 0, rmin2: rmax * 10., rmax2: rmax * 10., twist: false, numSegs: 24 });

			geo.applyMatrix( new THREE.Matrix4().makeTranslation( 0, 0, l * 10. ) );
			geo.applyMatrix( new THREE.Matrix4().makeRotationX( Math.PI ) );

			// eye, target, up
			geo.applyMatrix( new THREE.Matrix4().lookAt(
					new THREE.Vector3(0, 0, 0),
					new THREE.Vector3(
						l * 2. * 10. * Math.cos(h.phi),
						l * 2. * 10. * Math.sin(h.phi),
						l * 2. * 10. / Math.tan( 2.0 * Math.atan( Math.exp(-h.eta) ) )
					),
					new THREE.Vector3(0, 0, 1)
				)
			);

			if ( h['x0'] !== undefined && h['y0'] !== undefined && h['z0'] !== undefined ) {
				geo.applyMatrix( new THREE.Matrix4().makeTranslation( h['x0'] * 10., h['y0'] * 10., h['z0'] * 10. ) );
			}

			// face colors: array or single color
			let color = this.hitColorFunc( options.color, ratio );
			for ( let fc = 0; fc < geo.faces.length; fc++ ) {
				let face  = geo.faces[ fc ];
				face.color.setRGB( color.r, color.g, color.b );
			}

			combined_geometry.merge( geo );
		}

		three_node.add(
			new THREE.Mesh( combined_geometry,
				new THREE.MeshBasicMaterial({ color:  0xffffff, transparent: options.transparent ? true : false,
					opacity: options.transparent || 1, side: THREE.BackSide, vertexColors: THREE.FaceColors })
			)
		);
		three_node.add(
			new THREE.Mesh( combined_geometry,
				new THREE.MeshBasicMaterial({ color:  0xffffff, transparent: options.transparent ? true : false,
					opacity: options.transparent || 1, side: THREE.FrontSide, vertexColors: THREE.FaceColors })
			)
		);
	}

	get_track_color( trk ) {
		if ( trk.trk_color !== undefined && trk.trk_color !== false ) {
			return ( trk.trk_color | 0 );
		}
		switch( this.track_color_theme ) {
			case 'black':
				return 'rgb(0,0,0)';
			case 'white':
				return 'rgb(255,255,255)';
			case 'yellow':
				return 'rgb(255,244,0)';
			case 'blue':
			default: {
				let r = 0, g = 0, b = 0,
					p = Math.abs( trk['pt'] ),
					maxp = 4.5,
					colval = Math.min( 1.0, p / maxp ),
					colvaltimes4 = colval * 4.0;
				if ( colval < 0.25 ) {
					b = g = colvaltimes4;
					b += 1.0 - colvaltimes4;
				} else if ( colval < 0.5 ) {
					b = g = 1.0 - ( colvaltimes4 - 1.0 );
					g += colvaltimes4 - 1.0;
				} else if ( colval < 0.75 ) {
					g = r = colvaltimes4 - 2.0;
					g += 1.0 - ( colvaltimes4 - 2.0 );
				} else {
					g = r = 1.0 - ( colvaltimes4 - 3.0 );
					r += colvaltimes4 - 3.0;
				}
				if ( Math.random() < 0.01 ) { r = 1.0; }
				return 'rgb('+ ((255 * r)|0) +','+ ((255 * g)|0) +','+ ((255 * b)|0) +')';
			}
		}
	}

}
