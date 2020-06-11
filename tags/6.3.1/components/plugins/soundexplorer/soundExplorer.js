/**********************************************************************************
 WIKINDX : Bibliographic Management system.
 @link http://wikindx.sourceforge.net/ The WIKINDX SourceForge project
 @author The WIKINDX Team
 @license https://creativecommons.org/licenses/by-nc-sa/4.0/ CC-BY-NC-SA 4.0
**********************************************************************************/

/**
* Javascript functions for plugins/soundExplorer
*/


function sePlay(waveFormArray)
{
// create web audio api context
var audioCtx = new (window.AudioContext || window.webkitAudioContext)();
// create Oscillator node
var oscillator = audioCtx.createOscillator();

	for(var waveForm in waveFormArray)
	{
		if(waveFormArray[waveForm] == 'sine')
		{
			oscillator.type = 'sine';
			oscillator.frequency.value = 500; // value in hertz
		}
		else if(waveFormArray[waveForm] == 'square')
		{
			oscillator.type = 'square';
			oscillator.frequency.value = 500; // value in hertz
		}
		else //triangle
		{
			oscillator.type = 'triangle';
			oscillator.frequency.value = 500; // value in hertz
		}
		oscillator.connect(audioCtx.destination);
		oscillator.start();
		oscillator.stop(audioCtx.currentTime + 1); // 1 second after start
	}
}

function soundExplorerFunction(seFunction)
{
	sePlay();
}

function seTestSound()
{
	var waveForm = document.getElementById("seplugin_Sound").value;
	sePlay([waveForm]);
}

///////////////////

// Reset sound explorer status in parent window
function seChangeStatus(message)
{
	parent.opener.document.getElementById("soundExplorerStatus").innerHTML = decode_base64(message);
}
function decode_base64(s)
{
	var e={},i,k,v=[],r='',w=String.fromCharCode;
	var n=[[65,91],[97,123],[48,58],[43,44],[47,48]];
	
	for(z in n){for(i=n[z][0];i<n[z][1];i++){v.push(w(i));}}
	for(i=0;i<64;i++){e[v[i]]=i;}
	
	for(i=0;i<s.length;i+=72){
	var b=0,c,x,l=0,o=s.substring(i,i+72);
		 for(x=0;x<o.length;x++){
				c=e[o.charAt(x)];b=(b<<6)+c;l+=6;
				while(l>=8){r+=w((b>>>(l-=8))%256);}
		 }
	}
	return r;
}

