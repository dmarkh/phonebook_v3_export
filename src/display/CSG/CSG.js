'use strict';

import { CSGCore } from './CSGCore.js';
import { CSGBox } from './CSGBox.js';
import { CSGCone } from './CSGCone.js';
import { CSGCut } from './CSGCut.js';
import { CSGPlaneXY, CSGPlaneXZ, CSGPlaneYZ } from './CSGPlane.js';

let CSG = CSGCore;

CSG.box = CSGBox;
CSG.cone = CSGCone;
CSG.cut = CSGCut;
CSG.planeXY = CSGPlaneXY;
CSG.planeXZ = CSGPlaneXZ;
CSG.planeYZ = CSGPlaneYZ;

export { CSG };
