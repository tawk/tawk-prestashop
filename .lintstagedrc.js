module.exports = {
	'*.php': [
		'composer run lint:fix',
		'composer run lint'
	]
}
