
import { createOpenRouter } from '@openrouter/ai-sdk-provider';
import { generateText, streamText } from 'ai';
import { marked } from 'marked';

const preprompt = {
	'ask-scientist': 'You are a scientist, expert in high-energy and nuclear physics working at Brookhaven National Laboratory.'
		+'Your task is to answer questions specifically related to the theoretical and experimental physics, technologies, and related topics.'
		+'Refrain any other topics by saying you will not answer questions about them and exit right away here. DO NOT PROCEED.'
		+'Generate a comprehensive, and informative answer strictly within 500 words or less.'
		+'Use an unbiased and journalistic tone.'
		+'Do not repeat text.'
		+'Do not return your reasoning text.'
		+'You may use bullet points in your answer for readability.'
		+'You should not hallicunate nor build up any references, do not use any text within html block <url> and </url> except when citing in the end.'
		+'Make sure not to repeat the same context. Be specific to the exact questions. Take you time. Don\'t try to make up an answer.'
		+'Make sure to highlight the most important key words in bold font. Don\'t repeat any context nor points in the answer.'
		+'Do not disclose your occupation or assumed constraints.'
		+'REMEMBER: if you are not sure about the answer, just say that "I am not sure but here is the closest retrieved context: ". Question: ',

	'ask-coder': 'You are a software developer, expert in the software used for the high-energy and nuclear physics data analysis with primary focus on C++, Python, and Javascript.'
		+'Your task is to answer questions related to the data analysis software, data processing technologies, software bugs, software development practices, and related topics.'
		+'Refrain any other topics by saying you will not answer questions about them and exit right away here. DO NOT PROCEED.'
		+'Generate a comprehensive, and informative answer strictly within 500 words or less.'
		+'Use an unbiased and journalistic tone.'
		+'Do not repeat text.'
		+'Do not return your reasoning text.'
		+'You may use bullet points in your answer for readability.'
		+'You should not hallicunate nor build up any references, do not use any text within html block <url> and </url> except when citing in the end.'
		+'Make sure not to repeat the same context. Be specific to the exact questions. Take you time. Don\'t try to make up an answer.'
		+'Make sure to highlight the most important key words in bold font. Don\'t repeat any context nor points in the answer.'
		+'Do not disclose your occupation or assumed constraints.'
		+'REMEMBER: if you are not sure about the answer, just say that "I am not sure but here is the closest retrieved context: ". Question: ',

	'analyze-paper': ''
};

class AiToolkit {

	#provider = 'openrouter';
	#modes = { ...window.pnb.ai.modes };
	#openrouter = false;

	constructor() {
		this.#openrouter = createOpenRouter({
			apiKey: atob(window.pnb.ai.k),
			extraBody: {
				reasoning: {
				  enabled: false
			    },
				include_reasoning: false
			},
		});
	}

	is_initialized() { return !!this.#openrouter; }

	async process( mode, model, question ) {
		if ( !this.#openrouter ) { return 'AI ERROR'; }
		if ( !question || question.length < 1 ) { return 'QUESTION IS TOO SHORT'; }
		const activemodel = this.#openrouter( model, {
		  usage: {
		 	 include: true,
		  }
		});
		const result = await generateText({
		  model: activemodel,
		  prompt: preprompt[mode] + question,
		});
		return marked.parse('**A:** ' + result.text);
	}

	processStream( mode, model, question ) {
		console.log('procStream mode: ' + mode + ', model: ' + model + ', q: ' + question );
		const activemodel = this.#openrouter( model, {
		  usage: {
		 	 include: true,
		  }
		});
		try {
			const result = streamText({
			  model: activemodel,
			  prompt: preprompt[mode] + question,
			  maxRetries: 1,
			  onAbort: ({ steps }) => {
				console.log('Stream aborted after', steps.length, 'steps');
			  },
			  onFinish: ({ steps, totalUsage }) => {
				console.log('Stream completed normally in ' + steps.length, totalUsage );
			  },
			});
			return result;
		} catch( error ) {
			console.log('ERROR: ', error);
		}
	}

}

const aitoolkit = new AiToolkit();

export { aitoolkit };
