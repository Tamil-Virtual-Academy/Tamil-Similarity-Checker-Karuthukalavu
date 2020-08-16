<?php
?>

			<!-- <div class="dimensions"></div> -->
			<div class="description">தாங்கள் சோதிக்க வேண்டிய உரையை கீழ்காணும்
				உரைபெட்டியில் உள்ளிடுங்கள். உங்கள் உரையை கோப்பாகவும் கீழேயுள்ள
				கோப்பேற்றி வழியாக ஏற்றலாம். பிறகு கருத்துக்களவு சோதனையை துவக்க, வலது
				புறமுள்ள சோதி விசையை சொடுக்கவும்.</div>
			<div id='form' class="clear">
				<div class="field">
					<label>சோதிக்க வேண்டிய உரை:</label>
					<textarea id='qtext' name="qtext"></textarea>
					<label><input type="radio" name="ctype" value="thodar_thedal"/><span>இயல்பான தேடல்</span></label>
					<label><input type="radio" name="ctype" value="sol_thedal" checked><span>மேம்பட்ட தேடல்</span></label>
					<!--<label><input type="radio" name="ctype" value="inai_thedal"><span>இணைத்தொடர் தேடல்</span></label>-->
					<!-- <input type="file" id="qfile" name="qfile" disabled="disabled" style="display:none;" /> -->
				</div>
				<div class="field btn">
					<input id="btnCheck" type="button" class="button" value="சோதி" /> 
					<input id="btnClear" type="button" class="button" value="அழி" />
				</div>
			</div>
			<div id="status"><span id="msg"></span> <div class='loader' ><div class="l1"></div><div class="l2"></div><div class="l3"></div></div></div>
			<br/>
			<div id="results" class="clear">
				<div class='subheader'>சோதனை முடிவுகள்</div>
				<div>
					கட்டுரை களவு எண்:<span class='kennam'></span>
				</div>
				<div class='clear'>
					<div class='col' id='matches'></div>
					<div class='col' id='details'></div>
				</div>
				<div id="info" style="display: none;">
					<label>களவு எண்ணிக்கை:</label><span class='ennam'></span>
				</div>
			</div>
