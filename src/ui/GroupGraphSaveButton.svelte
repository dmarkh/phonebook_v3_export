<script>

import Button, { Label as ButtonLabel, Icon as ButtonIcon } from '@smui/button';
import { Panel, getNodesBounds, getViewportForBounds, useNodes } from '@xyflow/svelte';
import domtoimage from '../vendor/dom-to-image/dom-to-image.min.js';

const viewportnodes = useNodes();
const imageWidth  = 2000;
const imageHeight = 1500;

const saveImage = async () => {

    const nodesBounds = getNodesBounds($viewportnodes);
    const viewport = getViewportForBounds(nodesBounds, imageWidth, imageHeight, 0.5, 2.0, 0.2);
    // const viewportDomNode = document.getElementsByClassName('svelte-flow__viewport')[0];
    const viewportDomNode = document.querySelector('.svelte-flow__viewport');
    if ( viewport ) {
        let blob = await domtoimage.toBlob( viewportDomNode, {
            bgcolor: '#FFF',
            width: imageWidth,
            height: imageHeight,
            style: {
                width: `${imageWidth}px`,
                height: `${imageHeight}px`,
                transform: `translate(${viewport.x}px, ${viewport.y}px) scale(${viewport.zoom})`
            }
        });
        window.saveAs(blob, ( window.pnb.title ).split(' ').join('-').toLowerCase() + '-graph-' + new Date().toISOString().slice(0, 10) +'.png');
    }
}

</script>

      <Button on:click={saveImage}>
        <ButtonLabel>Save Image</ButtonLabel>
      </Button>