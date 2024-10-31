//Well Hello ...
document.onreadystatechange = () => {
	if (document.readyState === 'complete') {

		//loads/refreshes on any admin page load | some jQuery for simplicity
		//jQuery.post( ajaxurl,{ action: 'p_mini_ajax_titles_refresh' } ); 
		if( typeof mini_profiles_data == 'undefined'  ) return;
		if( typeof mini_profiles_data.profile_titles == 'undefined' || mini_profiles_data.profile_titles == '' ){
			document.getElementById("pmini-profiles").innerHTML += '<p style="color:red">' + 
			'<strong>Some required data not loaded, please try Updating Profiles in Plugin Settings</strong></p>'; 
			return;
		}

		let quotePindulaMiniProfileTitles = mini_profiles_data.profile_titles;
		const quotePindulaMiniProfilePostMeta = mini_profiles_data.savedProfiles.savedProfilesData;
		const quotePindulaMiniProfilePostMetaLowercase = mini_profiles_data.savedProfiles.lowerCaseTitles;

		/** 
		* current_page, quotePindulaMiniProfileTitles, 
		* quotePindulaMiniProfilePostMeta 
		* quotePindulaMiniProfilePostMetaLowercase pwiki-titles = quotePindulaMiniProfileTitles
		* above variables are defined in quote-pwiki.php
		* carrying over php processed letiables to client-side JS
		*/
		let metaBoxDiv = document.getElementById("pmini-profiles");
		let focusFlag = true; //ensures that the search stop after one click when focus away from tinymce
		let titlesFoundInContent = [];
		let newTitlesFound = [];
		let keyWordsToIgnore = [ "tl", "anc", "age", "the source" ];
		let printedTitlesCounter = 0; //track index of printed titles to add more titles' IDs
		
		var profileContainers = {
			"primaryId" : null,
			"selectedProfiles" : 0,
			"printedProfiles" : 0,
			"profiles" : {},
			"priorityValues" : [0, 1, 2]
		};
		//print any post meta if available
		if( quotePindulaMiniProfilePostMeta != '' ) {
			printMetaBox( quotePindulaMiniProfilePostMeta, false );   
		}   


		document.getElementById('search-mini-profiles').addEventListener('keyup', myFunc, false);

		document.querySelectorAll('.searchItem').forEach(function(item, index){
			item.addEventListener('click', addToList, false);
		});

		function myFunc(e){

			if( e.target.value == '' || e.target.value.length <= 3) {
				document.querySelectorAll('.searchItem').forEach(function(item, index){
					item.style.display = 'none';
				});
				document.getElementById('profilesList').classList.remove('scrollableList');
				return;
			}

		    filter = e.target.value.toLowerCase();
		    profilesList = document.getElementById('profilesList');
		    singleProfiles = profilesList.getElementsByTagName('li');

		    for (let i = 0; i < singleProfiles.length; i++) {
		        singleProfile = singleProfiles[i].getElementsByTagName('span')[0];

		        if (singleProfile.innerHTML.toLowerCase().indexOf(filter) > -1) {
		        	profilesList.classList.add('scrollableList');
		            singleProfile.style.display = 'block';
		        } else {
		            singleProfile.style.display = 'none';

		        }
		    }

		}

		function addToList(event){
			event.preventDefault(); 
			renderProfile( event.target.textContent, true );
		}
		
		let watch_editor = function watch_editor(){
			tinymce.get('content').on('blur', function(){
				if( focusFlag ){           
					let htmlTagsStrippedContent = (this.getContent()).toLowerCase().replace(/<\/?[^>]+(>|$)/g, ""); //strip html tags and remove links especially
					titlesFoundInContent = searchForMatchingTitle( htmlTagsStrippedContent );
					if( titlesFoundInContent.length != 0 ) printMetaBox( titlesFoundInContent, true );
					focusFlag = false;
				}
			});
			tinymce.get('content').on('focus', function(){ focusFlag = true; });
		}();
		
		function searchForMatchingTitle( content ){

			let temp = [];

			newTitlesFound.forEach( function(title, index){
				if( content.search( title.toLowerCase() ) == -1 ) {
					Object.keys(profileContainers.profiles).forEach(function(key) {
						if(  profileContainers.profiles[key].title == title ) {
							document.getElementById(key).remove();
							profileContainers.priorityValues.push( profileContainers.profiles[key].position );
							//profileContainers.priorityValues.sort();
							profileContainers.printedProfiles-=1;
							profileContainers.selectedProfiles-=1;
						}
					});
					newTitlesFound.splice(index, 1);
				}
			}); 

			quotePindulaMiniProfileTitles.forEach( function(item, index) {
			let titleInLowerCase = item.title.toLowerCase();
			try {
				if( ( content.search( titleInLowerCase.replace(/the /i, "") )) != -1 ) {
					
					if( ( newTitlesFound.indexOf( item.title ) == -1) && 
					//(quotePindulaMiniProfilePostMetaLowercase.indexOf( titleInLowerCase ) == -1 ) && 
					(keyWordsToIgnore.indexOf( titleInLowerCase ) == -1 ) ){
						temp.push( item.title );
						newTitlesFound.push( item.title );
					}
				}
			}catch (exception) {}
			});
			return temp ;
		}

		/* Prints the profiles/page titles found in Database or in the current text that's being edited */
		function printMetaBox( profiles, newValues = null ){
			if( newValues == null ) return

			for(profile in profiles) renderProfile( profiles[profile], newValues ? true : false );
		}
		
		function renderProfile(profile, isNew){
			
			let profileID = 'profile-' + profileContainers.printedProfiles;
			let profileTitle = '';
			let isPrimary = false;
			
			if( isNew ){
				
				//don't render duplicates
				let profileKeys = Object.keys(profileContainers.profiles);
				let isAlreadyInPage = false;
				for(let i = 0; i < profileKeys.length; i++){
					if( profileContainers.profiles[profileKeys[i]].title == profile ){
						isAlreadyInPage = true;
						break;
					}
				}

				if( isAlreadyInPage ) return;

				profileTitle = profile;
				profileContainers['profiles'][profileID] = {
					"title" : profileTitle,
					"checked" : false,
					"primary" : false
				}    
			}else{

				newTitlesFound.push( profile.title );//add this mini profile title to the list of printed ones to avoid duplicates
				profileTitle = profile.title;
				if( profile.position == 0 ){
					isPrimary = true;
					profileContainers['primaryId'] = profileID;
					var primaryButton = document.createElement('span');
					primaryButton.setAttribute( "style", "float:right");
					primaryButton.setAttribute( "id", "is-primary" );
					primaryButton.setAttribute( "primary", "is-primary" );
					primaryButton.textContent = "Primary";
				} 
				//remove current priority value
				profileContainers.priorityValues = profileContainers.priorityValues.filter(function(el){
					return el != profile.position;
				});
				profileContainers['profiles'][profileID] = {
					"title" : profile.title,
					"position" : profile.position,
					"checked" : true,
					"primary" : isPrimary
				}
			}
			
			if( !isPrimary ){
			//create primary profile/title toggle button
			var primaryButton = document.createElement('button');
			primaryButton.setAttribute( "type", "button" );
			primaryButton.setAttribute( "class", "make_primary" );
			primaryButton.setAttribute( "id", profileID );
			primaryButton.textContent = "Make Primary";
			primaryButton.addEventListener('click', onMakePrimaryClick, false);
			}
				
			//create profile container to hold all controls
			var profileDiv = document.createElement('div');
			profileDiv.setAttribute( "class", "profile-container" );
			profileDiv.setAttribute( "id", profileID );
			profileDiv.setAttribute("style", "margin: 15px auto;");
			//create profile/page title checkbox
			var profileCheckbox = document.createElement('input');
			profileCheckbox.setAttribute( "type", "checkbox" );
			//if has been selected already in previous edit set as checked
			isNew ? "" : profileCheckbox.setAttribute( "checked", "checked" );
			profileCheckbox.setAttribute( "class", "pwiki_input" );
			profileCheckbox.setAttribute( "id", profileID );
			profileCheckbox.setAttribute( "name", "pmini_profiles[]" );
			//if has been selected already in previous edit set profile set position
			if(isNew){ 
			profileCheckbox.setAttribute( "value", profileTitle );
			}else{
			profileCheckbox.setAttribute( "value", profileTitle + '_position=>' + profile.position );
			}
			profileCheckbox.addEventListener('click', onClickProfile, false);

			//create label element to show profile/page title
			var profileLabel = document.createElement('label');
			profileLabel.setAttribute( "title", 
			isNew ? "A preview will appear when you select Profile!" : profile['quote_pwiki_content'] );
			profileLabel.setAttribute( "for", profileID );
			profileLabel.textContent = profileTitle;
			
			//add all profile elements to parent div
			profileDiv.appendChild( profileCheckbox );
			profileDiv.appendChild( profileLabel );
			profileDiv.appendChild( primaryButton );
			metaBoxDiv.appendChild( profileDiv );
			//if has been selected already in previous edit increment already selected profiles counter 
			isNew ? "" : profileContainers.selectedProfiles += 1;
			profileContainers.printedProfiles += 1;

		}

		function onMakePrimaryClick( event ){
			if( event.target.id == profileContainers.primaryId || 
			!(profileContainers.profiles[event.target.id].checked) ) return;
		
			let selectedProfileID = event.target.id;
			let currentPrimaryID = profileContainers.primaryId;
			let selectedProfile = profileContainers.profiles[selectedProfileID];
			let currentPrimary = profileContainers.profiles[currentPrimaryID];
			let position = currentPrimary.position;
			currentPrimary.position = selectedProfile.position;
			selectedProfile.position = position;
			selectedProfile.primary = true;
			currentPrimary.primary = false;
			profileContainers.primaryId = selectedProfileID;
			//Indicate changes to user
			let currentPrimaryDiv = document.getElementById( currentPrimaryID );
			let selectedProfileDiv = document.getElementById( selectedProfileID );
			selectedProfileDiv.firstChild.value = selectedProfile.title + "_position=>" + selectedProfile.position;
			currentPrimaryDiv.firstChild.value = currentPrimary.title + "_position=>" + currentPrimary.position;
			//swap primary indicators
			let makePrimaryIndicator = selectedProfileDiv.lastChild;
			makePrimaryIndicator.setAttribute( "id", currentPrimaryID );
			//replace the curent primary span with the make primary button and keep the span for recycling
			let primarySpan = currentPrimaryDiv.replaceChild( makePrimaryIndicator, currentPrimaryDiv.lastChild );
			selectedProfileDiv.appendChild( primarySpan );
		}

		function onClickProfile( event ){
			
			selectedProfile = event.target.id;

			if( profileContainers.profiles[event.target.id].checked ){
				unselectProfile( event.target.id );
			}else{

				if( profileContainers.selectedProfiles >= 3 ) {
					event.preventDefault(); 
					return;
				}
				addProfile( event.target.id )
			}
		}

		function getProfileSnippet(articleTitle, selectedProfileLabel){
			//to avoid CORS erros we use the proxy https://cors-anywhere.herokuapp.com/
			var baseUrl = 'https://cors-anywhere.herokuapp.com/https://www.pindula.co.zw/api.php?action=query&format=json&formatversion=2' + 
			'&prop=extracts&redirects&exintro&explaintext&titles=';
			try {
				var xhttp = new XMLHttpRequest();
				xhttp.onreadystatechange = function() {
					if (this.readyState == 4 && this.status == 200) {
						selectedProfileLabel.title = JSON.parse(xhttp.responseText).query.pages["0"].extract;
						selectedProfileLabel.parentElement.classList.add('shake'); 
						setTimeout(function(){ 
							selectedProfileLabel.parentElement.classList.remove('shake'); 
						}, 800);
					}
				};
				xhttp.open("GET",  baseUrl + articleTitle, true);
				xhttp.send();
			}catch(error) { console.log(error); }
		}

		function addProfile( selectedProfile ){

				let profiles = profileContainers.profiles;
				profiles[selectedProfile].checked = true;
				profileContainers.priorityValues.sort();//ensures that smallest value comes out first
				profiles[selectedProfile].position = profileContainers.priorityValues.shift();
				profileContainers.selectedProfiles += 1;
				//Indicate changes to user
				let selectedProfileDiv = document.getElementById(selectedProfile);
				selectedProfileDiv.firstChild.value += "_position=>" + profiles[selectedProfile].position;
				if( selectedProfileDiv.children[1].title === 'A preview will appear when you select Profile!' ){
					getProfileSnippet( profiles[selectedProfile].title, selectedProfileDiv.children[1] );
				}
				
				if( profiles[selectedProfile].position == 0 ){
					var primaryButton = document.createElement('span');
					primaryButton.setAttribute( "style", "float:right");
					primaryButton.setAttribute( "id", "is-primary" );
					primaryButton.setAttribute( "primary", "is-primary" );
					primaryButton.textContent = "Primary";
					selectedProfileDiv.replaceChild( primaryButton, selectedProfileDiv.lastChild );
					profileContainers.primaryId = selectedProfile;
				}
		}


		function unselectProfile( selectedProfile ){
				let profiles = profileContainers.profiles;
				let currentPrimaryPos = profiles[selectedProfile].position;
				//if profile is set to primary do
				if( profileContainers.primaryId == selectedProfile ){
					//if only one profile is selected do
					if( profileContainers.selectedProfiles == 1 ){
						profileContainers.priorityValues.push( profiles[selectedProfile].position );
						profileContainers.selectedProfiles-=1;
						delete profiles[selectedProfile].position;
						profiles[selectedProfile].checked = false;
						profiles[selectedProfile].primary = false;
						//Indicate changes to user
						let selectedProfileDiv = document.getElementById(selectedProfile);
						let primaryButton = document.createElement('button');
						primaryButton.setAttribute( "class", "make_primary" );
						primaryButton.setAttribute( "id", selectedProfile);
						primaryButton.textContent = "Make Primary";
						selectedProfileDiv.firstChild.value = profiles[selectedProfile].title;
						selectedProfileDiv.replaceChild( primaryButton, selectedProfileDiv.lastChild);
						return;
					}
					let profileKeys = Object.keys(profiles);
					for(let i = 0; i < profileKeys.length; i++){
						if( profiles[profileKeys[i]].position > currentPrimaryPos ){
							nextHighPriorityProfile = profileKeys[i];
							break;
						}
					}
					
					profiles[selectedProfile].primary = false;
					profiles[nextHighPriorityProfile].primary = true;
					profileContainers.primaryId = nextHighPriorityProfile;
					profileContainers.priorityValues.push( profiles[nextHighPriorityProfile].position );
					profiles[nextHighPriorityProfile].position = profiles[selectedProfile].position;
					//Indicate changes to user
					let nextHighPriorityProfileDiv = document.getElementById(nextHighPriorityProfile);
					nextHighPriorityProfileDiv.firstChild.value = profiles[nextHighPriorityProfile].title + "_position=>" + profiles[nextHighPriorityProfile].position;
					//do //
					let makePrimaryIndicator = nextHighPriorityProfileDiv.replaceChild(document.getElementById(selectedProfile).lastChild, nextHighPriorityProfileDiv.lastChild);
					makePrimaryIndicator.setAttribute( "id", selectedProfile );
					document.getElementById(selectedProfile).appendChild( makePrimaryIndicator );
					document.getElementById(selectedProfile).firstChild.value = profiles[selectedProfile].title;


				}else{
					//if profile is not set to primary do
					profileContainers.priorityValues.push( profiles[selectedProfile].position );
					//Indicate changes to user
					document.getElementById(selectedProfile).firstChild.value = profiles[selectedProfile].title;
				}
				//do always
				profiles[selectedProfile].checked = false;
				document.getElementById(selectedProfile).firstChild.checked = false;
				delete profiles[selectedProfile].position;
				profileContainers.selectedProfiles-=1;
			}
			
	}
};
