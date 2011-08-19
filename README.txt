JORM - Joomla ORM Framework

Simple Framework to create a ORM for Joomla! Plataform. Object Role Modeling (ORM) is a powerful method for designing and querying database models at the conceptual level, JORM take Joomla! to the next level. Creating a new layer to access database, using a commom language for all devs. You can expand to 3rd extensions too.

Tutorials

1) Usgin Objects

Create a new user

jimport('jorm.import');
jimport('joomla.user.helper');

$user = JORMDatabaseQuery::getInstance('user');

$rows = $user->loadObjectList();

jimport('joomla.user.helper');

$user->name = 'julio pontes';
$user->username = 'julio.pontes';
$user->email = 'juliopfneto@gmail.com';
$user->password = JUserHelper::getCryptedPassword('1234');
$user->groups = array( 'registered' => 2 );
if( !$user->store() ) echo $user->getJTable()->getErrors();

Ps.: you can call Table fields, JTable, JDatabase methods;

2) using a table

You can create a instance from any table, filter by fields, use JDatabase methods.
If you set a JTable you can create, delete, update item.

Will we map the Newsfeed extension

JTable::addIncludepath(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_newsfeeds'.DS.'tables');

$newsfeed  = JORMDatabaseQuery::createInstance(array(
	'tbl' => '#__newsfeeds',
	'jtable' => array(
		'type' => 'Newsfeed',
		'prefix' => 'NewsfeedsTable'
	)
));


How to save a new newsfeed item

$newsfeed->name = 'new item';
$newsfeed->link = 'http://google.com';
$newsfeed->store();
if( !$newsfeed->store() )  echo $newsfeed->getJTable()->getErrors();


How to delete a item

$newsfeed->delete(1);

How to get a list of items

$rows = $newsfeed->loadObjectList();

Filter published

$rows = $newsfeed->published(1)->numarticles(5)->loadObjectList();

Ps.: You can use any JDatabase methods

Bugs?

Contact me juliopfneto@gmail.com